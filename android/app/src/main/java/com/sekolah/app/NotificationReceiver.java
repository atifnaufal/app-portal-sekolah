package com.sekolah.app;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.util.Log;

public class NotificationReceiver extends BroadcastReceiver {
    private static final String TAG = "NotificationReceiver";

    @Override
    public void onReceive(Context context, Intent intent) {
        Log.d(TAG, "Alarm received, ensuring BackgroundService is running");

        // The alarm acts as a watchdog to ensure the service is restarted if killed
        BackgroundService.startService(context);
    }
}
