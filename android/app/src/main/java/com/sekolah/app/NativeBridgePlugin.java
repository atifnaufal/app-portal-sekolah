package com.sekolah.app;

import android.Manifest;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.net.Uri;
import android.os.Build;
import android.provider.Settings;

import androidx.core.content.ContextCompat;

import com.getcapacitor.JSObject;
import com.getcapacitor.Plugin;
import com.getcapacitor.PluginCall;
import com.getcapacitor.PluginMethod;
import com.getcapacitor.annotation.CapacitorPlugin;

import java.util.ArrayList;
import java.util.List;

@CapacitorPlugin(name = "NativeBridge")
public class NativeBridgePlugin extends Plugin {

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
    public void saveToken(PluginCall call) {
        String token = call.getString("token");
        if (token == null) {
            call.reject("Token is required");
            return;
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
    public void getAppInfo(PluginCall call) {
        JSObject ret = new JSObject();
        ret.put("version", "1.1.0-Premium");
        ret.put("serviceRunning", true);
        call.resolve(ret);
    }
}
