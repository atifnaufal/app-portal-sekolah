package com.sekolah.app;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;

public class NotificationReceiver extends BroadcastReceiver {

    @Override
    public void onReceive(Context context, Intent intent) {
        NotificationHelper.createNotificationChannel(context);

        SharedPreferences prefs = context.getSharedPreferences("app_portal_prefs", Context.MODE_PRIVATE);
        String notificationCount = prefs.getString("notification_count", "0");

        if (!notificationCount.equals("0")) {
            NotificationHelper.showNotification(context, "Notifikasi Baru", notificationCount + " notifikasi baru");
        }
    }
}