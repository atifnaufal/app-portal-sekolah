package com.sekolah.app;

import android.Manifest;
import android.app.AlarmManager;
import android.content.Context;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.net.Uri;
import android.os.Build;
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

@CapacitorPlugin(name = "NativeBridge")
public class NativeBridgePlugin extends Plugin {

    @PluginMethod
    public void performBiometricAuth(PluginCall call) {
        getActivity().runOnUiThread(() -> {
            Executor executor = ContextCompat.getMainExecutor(getContext());
            BiometricPrompt biometricPrompt = new BiometricPrompt(getActivity(), executor, new BiometricPrompt.AuthenticationCallback() {
                @Override
                public void onAuthenticationError(int errorCode, @NonNull CharSequence errString) {
                    super.onAuthenticationError(errorCode, errString);
                    call.reject(errString.toString());
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
        int status = biometricManager.canAuthenticate(BiometricManager.Authenticators.BIOMETRIC_STRONG);
        JSObject ret = new JSObject();
        ret.put("isAvailable", status == BiometricManager.BIOMETRIC_SUCCESS);
        call.resolve(ret);
    }

    @PluginMethod
    public void savePin(PluginCall call) {
        String pin = call.getString("pin");
        if (pin == null || pin.length() < 4) {
            call.reject("PIN minimal 4 digit");
            return;
        }
        getContext().getSharedPreferences(AppConfig.PREFS_NAME, Context.MODE_PRIVATE)
                .edit().putString("app_pin", pin).apply();
        call.resolve();
    }

    @PluginMethod
    public void verifyPin(PluginCall call) {
        String pin = call.getString("pin");
        String savedPin = getContext().getSharedPreferences(AppConfig.PREFS_NAME, Context.MODE_PRIVATE)
                .getString("app_pin", "");
        JSObject ret = new JSObject();
        ret.put("isValid", savedPin.equals(pin));
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
}
