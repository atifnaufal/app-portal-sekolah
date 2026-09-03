package com.sekolah.app;

import android.util.Log;

import androidx.annotation.NonNull;

import com.google.firebase.messaging.FirebaseMessagingService;
import com.google.firebase.messaging.RemoteMessage;

public class MyFirebaseMessagingService extends FirebaseMessagingService {

    private static final String TAG = "FCMService";

    @Override
    public void onNewToken(@NonNull String token) {
        super.onNewToken(token);
        Log.d(TAG, "FCM Token diperbarui");
        BackgroundService.saveFcmToken(this, token);
        BackgroundService.registerFcmToken(this);
    }

    @Override
    public void onMessageReceived(@NonNull RemoteMessage remoteMessage) {
        super.onMessageReceived(remoteMessage);
        Log.d(TAG, "Pesan FCM diterima");

        if (remoteMessage.getNotification() != null) {
            String title = remoteMessage.getNotification().getTitle() != null
                    ? remoteMessage.getNotification().getTitle() : "Portal Sekolah";
            String body = remoteMessage.getNotification().getBody() != null
                    ? remoteMessage.getNotification().getBody() : "";
            String url = remoteMessage.getData().get("url");
            NotificationHelper.showNotification(this, title, body, url, (int) System.currentTimeMillis());
        }
    }
}