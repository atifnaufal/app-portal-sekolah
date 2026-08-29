package com.sekolah.app;

import android.app.AlarmManager;
import android.app.Notification;
import android.app.PendingIntent;
import android.app.Service;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Build;
import android.os.IBinder;
import android.os.PowerManager;
import android.os.SystemClock;
import android.util.Log;

import androidx.annotation.Nullable;
import androidx.core.app.NotificationCompat;

import java.io.IOException;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.concurrent.TimeUnit;

public class BackgroundService extends Service {

    private static final String TAG = "BackgroundService";
    private static final String PREFS_NAME = "app_portal_prefs";
    private static final String KEY_LAST_NOTIFICATION_ID = "last_notification_id";
    private static final String KEY_USER_ID = "user_id";
    private static final String KEY_TOKEN = "token";
    private static final String KEY_NOTIFICATION_COUNT = "notification_count";

    private static final long POLL_INTERVAL = 30000;
    private static final long WAKE_LOCK_TIMEOUT = 60000;

    private PowerManager.WakeLock wakeLock;
    private Thread pollThread;
    private volatile boolean running = false;

    @Nullable
    @Override
    public IBinder onBind(Intent intent) {
        return null;
    }

    @Override
    public void onCreate() {
        super.onCreate();
        Log.d(TAG, "BackgroundService created");
    }

    @Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        Log.d(TAG, "BackgroundService started");

        startForeground(1, createForegroundNotification());

        acquireWakeLock();

        running = true;
        startPolling();

        return START_STICKY;
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        Log.d(TAG, "BackgroundService destroyed");
        running = false;
        releaseWakeLock();
        if (pollThread != null) {
            pollThread.interrupt();
        }
    }

    private void startPolling() {
        pollThread = new Thread(() -> {
            while (running) {
                try {
                    pollNotifications();
                    pollSessionStatus();
                    TimeUnit.SECONDS.sleep(30);
                } catch (InterruptedException e) {
                    Thread.currentThread().interrupt();
                    break;
                } catch (Exception e) {
                    Log.e(TAG, "Error during polling: " + e.getMessage());
                }
            }
        }, "NotificationPollThread");
        pollThread.start();
    }

    private void pollNotifications() {
        SharedPreferences prefs = getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE);
        String token = prefs.getString(KEY_TOKEN, null);
        String lastIdStr = prefs.getString(KEY_LAST_NOTIFICATION_ID, "0");
        int lastId = lastIdStr.isEmpty() ? 0 : Integer.parseInt(lastIdStr);

        if (token == null || token.isEmpty()) {
            Log.d(TAG, "No token available, skipping notification poll");
            return;
        }

        try {
            String apiUrl = "https://app-portal-sekolah-production.up.railway.app/api/notifikasi/poll?last_id=" + lastId;
            URL url = new URL(apiUrl);
            HttpURLConnection connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.setRequestProperty("Authorization", "Bearer " + token);
            connection.setRequestProperty("Accept", "application/json");
            connection.setConnectTimeout(15000);
            connection.setReadTimeout(15000);

            int responseCode = connection.getResponseCode();
            if (responseCode == HttpURLConnection.HTTP_OK) {
                String response = readStream(connection);
                Log.d(TAG, "Poll response: " + response);

                try {
                    if (response.contains("\"new_last_id\"")) {
                        int newLastId = extractIntValue(response, "new_last_id");
                        int unreadCount = extractIntValue(response, "unread");

                        if (newLastId > lastId) {
                            prefs.edit().putString(KEY_LAST_NOTIFICATION_ID, String.valueOf(newLastId)).apply();

                            if (unreadCount > 0) {
                                prefs.edit().putString(KEY_NOTIFICATION_COUNT, String.valueOf(unreadCount)).apply();
                                showBackgroundNotification("Notifikasi Baru", unreadCount + " notifikasi baru");
                            }
                        }
                    }
                } catch (Exception e) {
                    Log.e(TAG, "Error parsing notification response: " + e.getMessage());
                }
            } else if (responseCode == 401) {
                Log.d(TAG, "Token expired, need re-authentication");
            }

            connection.disconnect();
        } catch (IOException e) {
            Log.e(TAG, "Error polling notifications: " + e.getMessage());
        }
    }

    private void pollSessionStatus() {
        SharedPreferences prefs = getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE);
        String token = prefs.getString(KEY_TOKEN, null);

        if (token == null || token.isEmpty()) {
            return;
        }

        try {
            String apiUrl = "https://app-portal-sekolah-production.up.railway.app/api/session/status";
            URL url = new URL(apiUrl);
            HttpURLConnection connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.setRequestProperty("Authorization", "Bearer " + token);
            connection.setRequestProperty("X-Requested-With", "XMLHttpRequest");
            connection.setConnectTimeout(10000);
            connection.setReadTimeout(10000);

            int responseCode = connection.getResponseCode();
            if (responseCode == HttpURLConnection.HTTP_OK) {
                String response = readStream(connection);
                if (response.contains("\"authenticated\":true")) {
                    Log.d(TAG, "Session still active");
                } else {
                    Log.d(TAG, "Session expired in background");
                }
            }

            connection.disconnect();
        } catch (IOException e) {
            Log.e(TAG, "Error polling session: " + e.getMessage());
        }
    }

    private void showBackgroundNotification(String title, String message) {
        NotificationHelper.createNotificationChannel(this);
        NotificationHelper.showNotification(this, title, message);

        playNotificationSound();
    }

    private void playNotificationSound() {
        try {
            android.media.MediaPlayer mediaPlayer = android.media.MediaPlayer.create(this, android.provider.Settings.System.DEFAULT_NOTIFICATION_URI);
            if (mediaPlayer != null) {
                mediaPlayer.start();
                mediaPlayer.setOnCompletionListener(mp -> {
                    try {
                        mp.release();
                    } catch (Exception e) {
                        Log.e(TAG, "Error releasing media player: " + e.getMessage());
                    }
                });
            }
        } catch (Exception e) {
            Log.e(TAG, "Error playing notification sound: " + e.getMessage());
        }
    }

    private void acquireWakeLock() {
        PowerManager powerManager = (PowerManager) getSystemService(Context.POWER_SERVICE);
        if (powerManager != null) {
            wakeLock = powerManager.newWakeLock(
                    PowerManager.PARTIAL_WAKE_LOCK,
                    "AppPortalSekolah::BackgroundServiceWakeLock"
            );
            wakeLock.acquire(WAKE_LOCK_TIMEOUT);
        }
    }

    private void releaseWakeLock() {
        if (wakeLock != null && wakeLock.isHeld()) {
            wakeLock.release();
            wakeLock = null;
        }
    }

    private Notification createForegroundNotification() {
        NotificationCompat.Builder builder = new NotificationCompat.Builder(this, "app_portal_sekolah_channel")
                .setContentTitle("App Portal Sekolah")
                .setContentText("Sedang aktif di latar belakang")
                .setSmallIcon(R.drawable.splash)
                .setPriority(NotificationCompat.PRIORITY_LOW)
                .setOngoing(true)
                .setSilent(true);

        return builder.build();
    }

    private String readStream(HttpURLConnection connection) throws IOException {
        StringBuilder sb = new StringBuilder();
        try (java.util.Scanner scanner = new java.util.Scanner(connection.getInputStream(), "UTF-8")) {
            while (scanner.hasNext()) {
                sb.append(scanner.nextLine());
            }
        }
        return sb.toString();
    }

    private int extractIntValue(String json, String key) {
        try {
            String searchKey = "\"" + key + "\"";
            int startIndex = json.indexOf(searchKey) + searchKey.length() + 1;
            int endIndex = json.indexOf(",", startIndex);
            if (endIndex == -1) {
                endIndex = json.indexOf("}", startIndex);
            }
            if (endIndex == -1) {
                endIndex = json.length();
            }
            String value = json.substring(startIndex, endIndex).trim();
            return Integer.parseInt(value);
        } catch (Exception e) {
            Log.e(TAG, "Error extracting value for key " + key + ": " + e.getMessage());
            return 0;
        }
    }

    public static void startService(Context context) {
        Intent intent = new Intent(context, BackgroundService.class);
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            context.startForegroundService(intent);
        } else {
            context.startService(intent);
        }

        scheduleAlarm(context);
    }

    public static void stopService(Context context) {
        Intent intent = new Intent(context, BackgroundService.class);
        context.stopService(intent);
    }

    private static void scheduleAlarm(Context context) {
        AlarmManager alarmManager = (AlarmManager) context.getSystemService(Context.ALARM_SERVICE);
        Intent intent = new Intent(context, NotificationReceiver.class);
        PendingIntent pendingIntent = PendingIntent.getBroadcast(context, 0, intent,
                PendingIntent.FLAG_UPDATE_CURRENT | PendingIntent.FLAG_IMMUTABLE);

        if (alarmManager != null) {
            long triggerAt = SystemClock.elapsedRealtime() + POLL_INTERVAL;
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                alarmManager.setAndAllowWhileIdle(AlarmManager.ELAPSED_REALTIME_WAKEUP, triggerAt, pendingIntent);
            } else {
                alarmManager.set(AlarmManager.ELAPSED_REALTIME_WAKEUP, triggerAt, pendingIntent);
            }
        }
    }

    public static void saveToken(Context context, String token) {
        SharedPreferences prefs = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE);
        prefs.edit().putString(KEY_TOKEN, token).apply();
    }

    public static void saveUserId(Context context, int userId) {
        SharedPreferences prefs = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE);
        prefs.edit().putInt(KEY_USER_ID, userId).apply();
    }

    public static String getToken(Context context) {
        SharedPreferences prefs = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE);
        return prefs.getString(KEY_TOKEN, null);
    }

    public static int getUserId(Context context) {
        SharedPreferences prefs = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE);
        return prefs.getInt(KEY_USER_ID, 0);
    }
}