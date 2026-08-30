package com.sekolah.app;

import android.Manifest;
import android.app.AlarmManager;
import android.app.DownloadManager;
import android.content.Context;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.database.Cursor;
import android.net.Uri;
import android.os.Build;
import android.os.Environment;
import android.os.PowerManager;
import android.provider.Settings;
import android.util.Log;

import androidx.annotation.NonNull;
import androidx.biometric.BiometricManager;
import androidx.biometric.BiometricPrompt;
import androidx.core.content.ContextCompat;

import com.getcapacitor.JSObject;
import com.getcapacitor.Plugin;
import com.getcapacitor.PluginCall;
import com.getcapacitor.PluginMethod;
import com.getcapacitor.annotation.CapacitorPlugin;

import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.Executor;
import java.security.MessageDigest;
import java.security.SecureRandom;

import javax.crypto.SecretKeyFactory;
import javax.crypto.spec.PBEKeySpec;

@CapacitorPlugin(name = "NativeBridge")
public class NativeBridgePlugin extends Plugin {

    @PluginMethod
    public void performBiometricAuth(PluginCall call) {
        if (getActivity() == null || getActivity().isFinishing()) {
            call.reject("Aktivitas tidak siap untuk autentikasi biometrik");
            return;
        }
        getActivity().runOnUiThread(() -> {
            Executor executor = ContextCompat.getMainExecutor(getContext());
            BiometricPrompt biometricPrompt = new BiometricPrompt(getActivity(), executor, new BiometricPrompt.AuthenticationCallback() {
                @Override
                public void onAuthenticationError(int errorCode, @NonNull CharSequence errString) {
                    super.onAuthenticationError(errorCode, errString);
                    // User tapped "Use PIN" button — this is not an error
                    if (errorCode == BiometricPrompt.ERROR_NEGATIVE_BUTTON ||
                        errorCode == BiometricPrompt.ERROR_USER_CANCELED) {
                        JSObject ret = new JSObject();
                        ret.put("cancelled", true);
                        call.resolve(ret);
                    } else {
                        call.reject(errString.toString());
                    }
                }

                @Override
                public void onAuthenticationSucceeded(@NonNull BiometricPrompt.AuthenticationResult result) {
                    super.onAuthenticationSucceeded(result);
                    call.resolve();
                }

                @Override
                public void onAuthenticationFailed() {
                    super.onAuthenticationFailed();
                    // Failed attempt, usually handled by system dialog, but we can notify JS
                }
            });

            BiometricPrompt.PromptInfo promptInfo = new BiometricPrompt.PromptInfo.Builder()
                    .setTitle("Autentikasi Diperlukan")
                    .setSubtitle("Gunakan biometrik Anda untuk melanjutkan")
                    .setAllowedAuthenticators(BiometricManager.Authenticators.BIOMETRIC_WEAK)
                    .setNegativeButtonText("Gunakan PIN/Password")
                    .build();

            biometricPrompt.authenticate(promptInfo);
        });
    }

    @PluginMethod
    public void checkPermissionsStatus(PluginCall call) {
        JSObject ret = new JSObject();
        List<String> missing = new ArrayList<>();

        if (ContextCompat.checkSelfPermission(getContext(), Manifest.permission.ACCESS_FINE_LOCATION) != PackageManager.PERMISSION_GRANTED) {
            missing.add("Lokasi");
        }
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            if (ContextCompat.checkSelfPermission(getContext(), Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED) {
                missing.add("Notifikasi");
            }
        }
        if (ContextCompat.checkSelfPermission(getContext(), Manifest.permission.CAMERA) != PackageManager.PERMISSION_GRANTED) {
            missing.add("Kamera");
        }

        ret.put("isComplete", missing.isEmpty());
        ret.put("missingPermissions", String.join(", ", missing));
        call.resolve(ret);
    }

    @PluginMethod
    public void openAppSettings(PluginCall call) {
        Intent intent = new Intent(Settings.ACTION_APPLICATION_DETAILS_SETTINGS);
        Uri uri = Uri.fromParts("package", getContext().getPackageName(), null);
        intent.setData(uri);
        getContext().startActivity(intent);
        call.resolve();
    }

    @PluginMethod
    public void startService(PluginCall call) {
        BackgroundService.startService(getContext());
        call.resolve();
    }

    @PluginMethod
    public void stopService(PluginCall call) {
        BackgroundService.stopService(getContext());
        call.resolve();
    }

    @PluginMethod
    public void checkBiometricSupport(PluginCall call) {
        BiometricManager biometricManager = BiometricManager.from(getContext());
        // BIOMETRIC_WEAK supports secure face unlock as well as fingerprints. Requiring
        // STRONG made many otherwise compatible devices appear unsupported.
        int status = biometricManager.canAuthenticate(BiometricManager.Authenticators.BIOMETRIC_WEAK);
        JSObject ret = new JSObject();
        ret.put("isAvailable", status == BiometricManager.BIOMETRIC_SUCCESS);
        call.resolve(ret);
    }

    @PluginMethod
    public void savePin(PluginCall call) {
        String pin = call.getString("pin");
        // Empty string = remove PIN
        if (pin == null) {
            call.reject("PIN tidak valid");
            return;
        }
        if (pin.isEmpty()) {
            // Remove PIN
            getContext().getSharedPreferences(AppConfig.PREFS_NAME, Context.MODE_PRIVATE)
                    .edit()
                    .remove("app_pin")
                    .remove("app_pin_hash")
                    .remove("app_pin_salt")
                    .remove("app_pin_length")
                    .apply();
            call.resolve();
            return;
        }
        if (pin.length() < 4 || pin.length() > 6) {
            call.reject("PIN harus 4-6 digit");
            return;
        }
        try {
            byte[] salt = new byte[16];
            new SecureRandom().nextBytes(salt);
            String hash = hashPin(pin, salt);
            getContext().getSharedPreferences(AppConfig.PREFS_NAME, Context.MODE_PRIVATE)
                    .edit()
                    .putString("app_pin_hash", hash)
                    .putString("app_pin_salt", android.util.Base64.encodeToString(salt, android.util.Base64.NO_WRAP))
                    .putInt("app_pin_length", pin.length())
                    .remove("app_pin")
                    .apply();
            call.resolve();
        } catch (Exception e) {
            call.reject("Gagal mengamankan PIN", e);
        }
    }

    @PluginMethod
    public void verifyPin(PluginCall call) {
        String pin = call.getString("pin");
        android.content.SharedPreferences prefs = getContext().getSharedPreferences(AppConfig.PREFS_NAME, Context.MODE_PRIVATE);
        String savedHash = prefs.getString("app_pin_hash", "");
        String legacyPin = prefs.getString("app_pin", "");
        JSObject ret = new JSObject();
        try {
            boolean valid;
            if (!savedHash.isEmpty()) {
                String saltText = prefs.getString("app_pin_salt", "");
                byte[] salt = android.util.Base64.decode(saltText, android.util.Base64.NO_WRAP);
                valid = MessageDigest.isEqual(savedHash.getBytes(java.nio.charset.StandardCharsets.UTF_8), hashPin(pin, salt).getBytes(java.nio.charset.StandardCharsets.UTF_8));
            } else {
                // One-time migration for PINs created by earlier APK versions.
                valid = !legacyPin.isEmpty() && legacyPin.equals(pin);
                if (valid) {
                    byte[] salt = new byte[16];
                    new SecureRandom().nextBytes(salt);
                    prefs.edit()
                            .putString("app_pin_hash", hashPin(pin, salt))
                            .putString("app_pin_salt", android.util.Base64.encodeToString(salt, android.util.Base64.NO_WRAP))
                            .putInt("app_pin_length", pin.length())
                            .remove("app_pin")
                            .apply();
                }
            }
            ret.put("isValid", valid);
            call.resolve(ret);
        } catch (Exception e) {
            call.reject("Gagal memverifikasi PIN", e);
        }
    }

    @PluginMethod
    public void getPinLength(PluginCall call) {
        android.content.SharedPreferences prefs = getContext().getSharedPreferences(AppConfig.PREFS_NAME, Context.MODE_PRIVATE);
        int length = prefs.getInt("app_pin_length", prefs.getString("app_pin", "").length());
        JSObject ret = new JSObject();
        ret.put("length", length);
        ret.put("isSet", length > 0);
        call.resolve(ret);
    }

    @PluginMethod
    public void checkBatteryExemption(PluginCall call) {
        PowerManager pm = (PowerManager) getContext().getSystemService(Context.POWER_SERVICE);
        JSObject ret = new JSObject();
        ret.put("isExempted", pm != null && pm.isIgnoringBatteryOptimizations(getContext().getPackageName()));
        call.resolve(ret);
    }

    @PluginMethod
    public void saveToken(PluginCall call) {
        String token = call.getString("token");
        String baseUrl = call.getString("baseUrl");
        if (token == null) {
            call.reject("Token is required");
            return;
        }
        if (baseUrl != null && !baseUrl.isEmpty()) {
            // Append /api if not present
            if (!baseUrl.endsWith("/api")) {
                baseUrl = baseUrl + "/api";
            }
            getContext().getSharedPreferences(AppConfig.PREFS_NAME, Context.MODE_PRIVATE)
                    .edit().putString("api_base_url", baseUrl).apply();
        }
        BackgroundService.saveToken(getContext(), token);
        BackgroundService.startService(getContext());
        call.resolve();
    }

    @PluginMethod
    public void saveUserId(PluginCall call) {
        Integer userId = call.getInt("userId");
        if (userId == null) {
            call.reject("userId is required");
            return;
        }
        BackgroundService.saveUserId(getContext(), userId);
        call.resolve();
    }

    @PluginMethod
    public void requestBatteryExemption(PluginCall call) {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            Intent intent = new Intent();
            String packageName = getContext().getPackageName();
            PowerManager pm = (PowerManager) getContext().getSystemService(Context.POWER_SERVICE);
            if (pm != null && !pm.isIgnoringBatteryOptimizations(packageName)) {
                intent.setAction(Settings.ACTION_REQUEST_IGNORE_BATTERY_OPTIMIZATIONS);
                intent.setData(Uri.parse("package:" + packageName));
                getContext().startActivity(intent);
            }
        }
        call.resolve();
    }

    @PluginMethod
    public void checkExactAlarmSupport(PluginCall call) {
        JSObject ret = new JSObject();
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
            AlarmManager alarmManager = (AlarmManager) getContext().getSystemService(Context.ALARM_SERVICE);
            ret.put("isGranted", alarmManager != null && alarmManager.canScheduleExactAlarms());
        } else {
            ret.put("isGranted", true);
        }
        call.resolve(ret);
    }

    @PluginMethod
    public void requestExactAlarmPermission(PluginCall call) {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
            Intent intent = new Intent(Settings.ACTION_REQUEST_SCHEDULE_EXACT_ALARM);
            intent.setData(Uri.parse("package:" + getContext().getPackageName()));
            getContext().startActivity(intent);
        }
        call.resolve();
    }

    @PluginMethod
    public void getAppInfo(PluginCall call) {
        JSObject ret = new JSObject();
        ret.put("version", "1.1.0-Premium");
        ret.put("serviceRunning", BackgroundService.isRunning());
        call.resolve(ret);
    }

    @PluginMethod
    public void downloadFile(PluginCall call) {
        String url = call.getString("url");
        String filename = call.getString("filename");

        if (url == null || url.isEmpty()) {
            call.reject("URL is required");
            return;
        }

        try {
            DownloadManager.Request request;
            if (filename != null && !filename.isEmpty()) {
                request = new DownloadManager.Request(Uri.parse(url));
                request.setTitle(filename);
                request.setDescription("Mengunduh file...");
            } else {
                request = new DownloadManager.Request(Uri.parse(url));
                // Extract filename from URL
                String path = Uri.parse(url).getPath();
                String fileName = path != null ? path.substring(path.lastIndexOf('/') + 1) : "download";
                request.setTitle(fileName);
                request.setDescription("Mengunduh file...");
            }

            request.setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED);
            request.setDestinationInExternalPublicDir(Environment.DIRECTORY_DOWNLOADS, filename != null ? filename : "download");
            request.setAllowedOverMetered(true);
            request.setAllowedOverRoaming(true);

            DownloadManager manager = (DownloadManager) getContext().getSystemService(Context.DOWNLOAD_SERVICE);
            if (manager != null) {
                long downloadId = manager.enqueue(request);
                JSObject ret = new JSObject();
                ret.put("downloadId", downloadId);
                ret.put("success", true);
                call.resolve(ret);
            } else {
                call.reject("Download Manager tidak tersedia");
            }
        } catch (Exception e) {
            Log.e("NativeBridge", "Download error: " + e.getMessage());
            call.reject("Gagal mengunduh: " + e.getMessage());
        }
    }

    private String hashPin(String pin, byte[] salt) throws Exception {
        PBEKeySpec spec = new PBEKeySpec(pin.toCharArray(), salt, 120000, 256);
        try {
            byte[] encoded = SecretKeyFactory.getInstance("PBKDF2WithHmacSHA256").generateSecret(spec).getEncoded();
            return android.util.Base64.encodeToString(encoded, android.util.Base64.NO_WRAP);
        } finally {
            spec.clearPassword();
        }
    }
}
