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

public class MainActivity extends BridgeActivity {

    private static final String TAG = "MainActivity";
    private static final int PERMISSION_REQUEST_CODE = 100;
    private static final int MAX_PERMISSION_RETRY = 3;

    private static final String[] REQUIRED_PERMISSIONS = {
            Manifest.permission.INTERNET,
            Manifest.permission.READ_MEDIA_IMAGES,
            Manifest.permission.READ_MEDIA_VIDEO,
            Manifest.permission.READ_MEDIA_AUDIO,
            Manifest.permission.RECORD_AUDIO,
            Manifest.permission.POST_NOTIFICATIONS,
            Manifest.permission.ACCESS_FINE_LOCATION,
            Manifest.permission.ACCESS_COARSE_LOCATION,
            Manifest.permission.VIBRATE,
    };

    private int permissionRetryCount = 0;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        requestAllPermissions();
        startBackgroundService();
    }

    private void requestAllPermissions() {
        if (Build.VERSION.SDK_INT < Build.VERSION_CODES.TIRAMISU) {
            return;
        }

        boolean allPermissionsGranted = true;
        for (String permission : REQUIRED_PERMISSIONS) {
            if (ContextCompat.checkSelfPermission(this, permission) != PackageManager.PERMISSION_GRANTED) {
                allPermissionsGranted = false;
                break;
            }
        }

        if (!allPermissionsGranted) {
            ActivityCompat.requestPermissions(this, REQUIRED_PERMISSIONS, PERMISSION_REQUEST_CODE);
        }
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull String[] permissions, @NonNull int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);

        if (requestCode == PERMISSION_REQUEST_CODE) {
            boolean allGranted = true;
            boolean shouldShowRationale = false;

            for (int i = 0; i < grantResults.length; i++) {
                if (grantResults[i] != PackageManager.PERMISSION_GRANTED) {
                    allGranted = false;
                    if (shouldShowRequestPermissionRationale(permissions[i])) {
                        shouldShowRationale = true;
                    }
                }
            }

            if (allGranted) {
                permissionRetryCount = 0;
                Log.d(TAG, "All permissions granted");
            } else if (shouldShowRationale && permissionRetryCount < MAX_PERMISSION_RETRY) {
                permissionRetryCount++;
                ActivityCompat.requestPermissions(this, REQUIRED_PERMISSIONS, PERMISSION_REQUEST_CODE);
                Log.d(TAG, "Re-requesting permissions, attempt: " + permissionRetryCount);
            } else if (!shouldShowRationale && permissionRetryCount < MAX_PERMISSION_RETRY) {
                permissionRetryCount++;
                ActivityCompat.requestPermissions(this, REQUIRED_PERMISSIONS, PERMISSION_REQUEST_CODE);
                Log.d(TAG, "Re-requesting permissions (don't ask again), attempt: " + permissionRetryCount);
            } else {
                Log.w(TAG, "Max permission retry reached, some permissions denied");
            }
        }
    }

    private void startBackgroundService() {
        try {
            NotificationHelper.createNotificationChannel(this);
            BackgroundService.startService(this);
            Log.d(TAG, "Background service started");
        } catch (Exception e) {
            Log.e(TAG, "Error starting background service: " + e.getMessage());
        }
    }

    public void saveAuthToken(String token) {
        BackgroundService.saveToken(this, token);
    }

    public void saveUserId(int userId) {
        BackgroundService.saveUserId(this, userId);
    }
}