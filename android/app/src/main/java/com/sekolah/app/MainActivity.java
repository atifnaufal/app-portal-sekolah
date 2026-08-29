package com.sekolah.app;

import android.Manifest;
import android.app.AlertDialog;
import android.content.Context;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.os.PowerManager;
import android.provider.Settings;
import android.util.Log;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;

import com.getcapacitor.BridgeActivity;

import java.util.ArrayList;
import java.util.List;

public class MainActivity extends BridgeActivity {

    private static final String TAG = "MainActivity";
    private static final int PERMISSION_REQUEST_CODE = 100;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // Register custom plugins
        registerPlugin(NativeBridgePlugin.class);

        checkAndRequestPermissions();
        checkBatteryOptimization();
        startBackgroundService();

        // Ensure web view doesn't handle everything if permissions missing
        handleForcedPermissions();
    }

    @Override
    protected void onResume() {
        super.onResume();
        handleForcedPermissions();
    }

    private void handleForcedPermissions() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            if (ContextCompat.checkSelfPermission(this, Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED) {
                // We can't easily block Capacitor webview without a custom layout,
                // but we can show a persistent blocking dialog.
                showMandatoryPermissionDialog();
            }
        }
    }

    private void showMandatoryPermissionDialog() {
        new AlertDialog.Builder(this)
                .setTitle("Akses Dibatasi")
                .setMessage("Aplikasi ini memerlukan izin Notifikasi untuk berfungsi sebagai asisten akademik Anda. Mohon izinkan melalui Pengaturan.")
                .setCancelable(false)
                .setPositiveButton("Buka Pengaturan", (dialog, which) -> openAppSettings())
                .setNegativeButton("Keluar", (dialog, which) -> finish())
                .show();
    }

    private void checkAndRequestPermissions() {
        List<String> permissionsNeeded = new ArrayList<>();

        // Basic permissions
        permissionsNeeded.add(Manifest.permission.INTERNET);

        // Media and Notification permissions
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.POST_NOTIFICATIONS);
            addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.READ_MEDIA_IMAGES);
        } else {
            addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.READ_EXTERNAL_STORAGE);
        }

        // Functional permissions
        addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.CAMERA);
        addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.ACCESS_FINE_LOCATION);

        // Foreground service permissions for Android 14+
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.UPSIDE_DOWN_CAKE) {
            addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.FOREGROUND_SERVICE_DATA_SYNC);
            addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.FOREGROUND_SERVICE_LOCATION);
        }

        if (!permissionsNeeded.isEmpty()) {
            ActivityCompat.requestPermissions(this, permissionsNeeded.toArray(new String[0]), PERMISSION_REQUEST_CODE);
        }
    }

    private void checkBatteryOptimization() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            Intent intent = new Intent();
            String packageName = getPackageName();
            PowerManager pm = (PowerManager) getSystemService(Context.POWER_SERVICE);
            if (pm != null && !pm.isIgnoringBatteryOptimizations(packageName)) {
                intent.setAction(Settings.ACTION_REQUEST_IGNORE_BATTERY_OPTIMIZATIONS);
                intent.setData(Uri.parse("package:" + packageName));
                try {
                    startActivity(intent);
                } catch (Exception e) {
                    Log.e(TAG, "Battery optimization request failed: " + e.getMessage());
                }
            }
        }
    }

    private void addPermissionIfNotGranted(List<String> list, String permission) {
        if (ContextCompat.checkSelfPermission(this, permission) != PackageManager.PERMISSION_GRANTED) {
            list.add(permission);
        }
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull String[] permissions, @NonNull int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        if (requestCode == PERMISSION_REQUEST_CODE) {
            boolean allGranted = true;
            boolean shouldShowRationale = false;

            for (int i = 0; i < permissions.length; i++) {
                if (grantResults[i] != PackageManager.PERMISSION_GRANTED) {
                    allGranted = false;
                    // Check if critical permissions were denied permanently
                    if (!ActivityCompat.shouldShowRequestPermissionRationale(this, permissions[i])) {
                        shouldShowRationale = true;
                    }
                }
            }

            if (!allGranted && shouldShowRationale) {
                showPermissionDeniedDialog();
            } else if (allGranted) {
                Log.d(TAG, "All permissions granted");
                startBackgroundService();
            }
        }
    }

    private void showPermissionDeniedDialog() {
        new AlertDialog.Builder(this)
                .setTitle("Perijinan Diperlukan")
                .setMessage("Applikasi memerlukan perijinan Notifikasi dan Lokasi agar fitur latar belakang berjalan optimal. Silakan aktifkan di Pengaturan.")
                .setPositiveButton("Buka Pengaturan", (dialog, which) -> openAppSettings())
                .setNegativeButton("Nanti", null)
                .show();
    }

    private void openAppSettings() {
        Intent intent = new Intent(Settings.ACTION_APPLICATION_DETAILS_SETTINGS);
        Uri uri = Uri.fromParts("package", getPackageName(), null);
        intent.setData(uri);
        startActivity(intent);
    }

    private void startBackgroundService() {
        try {
            BackgroundService.startService(this);
            Log.d(TAG, "Background service initiated from MainActivity");
        } catch (Exception e) {
            Log.e(TAG, "Error starting background service: " + e.getMessage());
        }
    }
}
