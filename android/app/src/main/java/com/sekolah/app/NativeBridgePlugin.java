package com.sekolah.app;

import com.getcapacitor.JSObject;
import com.getcapacitor.Plugin;
import com.getcapacitor.PluginCall;
import com.getcapacitor.PluginMethod;
import com.getcapacitor.annotation.CapacitorPlugin;

@CapacitorPlugin(name = "NativeBridge")
public class NativeBridgePlugin extends Plugin {

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
