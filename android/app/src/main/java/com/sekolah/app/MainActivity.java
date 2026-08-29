package com.sekolah.app;

import android.Manifest;
import android.content.pm.PackageManager;
import android.os.Build;
import android.os.Bundle;
import android.util.Log;

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

        checkAndRequestPermissions();
        startBackgroundService();
    }

    private void checkAndRequestPermissions() {
        List<String> permissionsNeeded = new ArrayList<>();

        // Basic permissions
        permissionsNeeded.add(Manifest.permission.INTERNET);

        // Media permissions based on Android version
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.READ_MEDIA_IMAGES);
            addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.READ_MEDIA_VIDEO);
            addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.READ_MEDIA_AUDIO);
            addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.POST_NOTIFICATIONS);
        } else {
            addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.READ_EXTERNAL_STORAGE);
            addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.WRITE_EXTERNAL_STORAGE);
        }

        // Functional permissions
        addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.CAMERA);
        addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.RECORD_AUDIO);
        addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.ACCESS_FINE_LOCATION);
        addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.ACCESS_COARSE_LOCATION);

        // Foreground service permissions for Android 14+
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.UPSIDE_DOWN_CAKE) {
            addPermissionIfNotGranted(permissionsNeeded, Manifest.permission.FOREGROUND_SERVICE_DATA_SYNC);
        }

        if (!permissionsNeeded.isEmpty()) {
            ActivityCompat.requestPermissions(this, permissionsNeeded.toArray(new String[0]), PERMISSION_REQUEST_CODE);
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
            Log.d(TAG, "Permissions updated");
            // Optionally notify the web layer that permissions changed
        }
    }

    private void startBackgroundService() {
        try {
            BackgroundService.startService(this);
            Log.d(TAG, "Background service initiated from MainActivity");
        } catch (Exception e) {
            Log.e(TAG, "Error starting background service: " + e.getMessage());
        }
    }

    // Bridge methods called from Capacitor/JS
    public void saveAuthToken(String token) {
        BackgroundService.saveToken(this, token);
        // Restart service to use the new token immediately
        BackgroundService.startService(this);
    }

    public void saveUserId(int userId) {
        BackgroundService.saveUserId(this, userId);
    }
}
