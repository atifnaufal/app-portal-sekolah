package com.sekolah.app;

import android.Manifest;
import android.app.AlarmManager;
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
        // Capacitor discovers custom plugins while creating its bridge, so this must
        // happen before BridgeActivity.onCreate().
        registerPlugin(NativeBridgePlugin.class);
        super.onCreate(savedInstanceState);

        checkAndRequestPermissions();
        checkBatteryOptimization();
        checkExactAlarmPermission();
        // The service is started after a successful login when an API token exists.
        // Starting it for every fresh install causes needless foreground notifications.
        if (BackgroundService.hasToken(this)) {
            startBackgroundService();
        }

        // Ensure web view doesn't handle everything if permissions missing
        handleForcedPermissions();
    }

    private void checkExactAlarmPermission() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
            AlarmManager alarmManager = (AlarmManager) getSystemService(Context.ALARM_SERVICE);
            if (alarmManager != null && !alarmManager.canScheduleExactAlarms()) {
                Log.w(TAG, "Exact alarm permission not granted - scheduling might be delayed");
            }
        }
    }

    @Override
    public void onResume() {
        super.onResume();
        handleForcedPermissions();
    }

    private void handleForcedPermissions() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            if (ContextCompat.checkSelfPermission(this, Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED) {
                showMandatoryPermissionDialog();
            }
        }
    }

    private void showMandatoryPermissionDialog() {
        new AlertDialog.Builder(this)
                .setTitle("Izin Notifikasi Diperlukan")
                .setMessage("Aplikasi Portal Sekolah memerlukan izin Notifikasi untuk mengirimkan info tugas dan pengumuman secara tepat waktu. Mohon aktifkan di Pengaturan.")
                .setCancelable(false)
                .setPositiveButton("Buka Pengaturan", (dialog, which) -> openAppSettings())
                .setNegativeButton("Keluar", (dialog, which) -> finish())
                .show();
    }

    private void checkAndRequestPermissions() {
        List<String> permissionsNeeded = new ArrayList<>();

        // Media and Notification permissions
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.POST_NOTIFICATIONS);
            addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.READ_MEDIA_IMAGES);
            addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.READ_MEDIA_VIDEO);
        } else {
            addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.READ_EXTERNAL_STORAGE);
        }

        // Functional permissions
        addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.CAMERA);
        addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.ACCESS_FINE_LOCATION);
        addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.RECORD_AUDIO);

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
            PowerManager pm = (PowerManager) getSystemService(Context.POWER_SERVICE);
            if (pm != null && !pm.isIgnoringBatteryOptimizations(getPackageName())) {
                Log.d(TAG, "Battery optimization is active - background tasks may be throttled");
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
            for (int i = 0; i < permissions.length; i++) {
                if (grantResults[i] != PackageManager.PERMISSION_GRANTED) {
                    allGranted = false;
                }
            }
            if (allGranted && BackgroundService.hasToken(this)) {
                Log.d(TAG, "All permissions granted");
                startBackgroundService();
            }
        }
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
