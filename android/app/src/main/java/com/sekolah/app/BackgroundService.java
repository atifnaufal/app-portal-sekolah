package com.sekolah.app;

import android.app.AlarmManager;
import android.app.Notification;
import android.app.PendingIntent;
import android.app.Service;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.pm.ServiceInfo;
import android.os.Build;
import android.os.IBinder;
import android.os.PowerManager;
import android.os.SystemClock;
import android.util.Log;

import androidx.core.app.NotificationCompat;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.TimeUnit;

public class BackgroundService extends Service {

    private static final String TAG = "BackgroundService";
    private PowerManager.WakeLock wakeLock;
    private ExecutorService executorService;
    private volatile boolean running = false;
    private static volatile boolean isServiceRunning = false;

    public static boolean isRunning() {
        return isServiceRunning;
    }

    @Override
    public void onCreate() {
        super.onCreate();
        Log.d(TAG, "BackgroundService created");
        executorService = Executors.newSingleThreadExecutor();
        isServiceRunning = true;
    }

    @Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        Log.d(TAG, "BackgroundService onStartCommand");

        // Start as foreground service to avoid being killed easily
        Notification notification = createForegroundNotification();
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.UPSIDE_DOWN_CAKE) {
            startForeground(AppConfig.FOREGROUND_SERVICE_ID, notification,
                ServiceInfo.FOREGROUND_SERVICE_TYPE_DATA_SYNC | ServiceInfo.FOREGROUND_SERVICE_TYPE_LOCATION);
        } else {
            startForeground(AppConfig.FOREGROUND_SERVICE_ID, notification);
        }

        acquireWakeLock();

        if (!running) {
            running = true;
            startPollingTask();
        }

        // Schedule next alarm in case the service is killed
        scheduleAlarm(this);

        return START_STICKY;
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        Log.d(TAG, "BackgroundService destroyed");
        running = false;
        isServiceRunning = false;
        if (executorService != null) {
            executorService.shutdownNow();
        }
        releaseWakeLock();
    }

    @Override
    public IBinder onBind(Intent intent) {
        return null;
    }

    private void startPollingTask() {
        executorService.execute(() -> {
            while (running) {
                try {
                    boolean success = pollNotifications();
                    pollSessionStatus();

                    // Sleep for the defined interval
                    TimeUnit.MILLISECONDS.sleep(AppConfig.POLL_INTERVAL_MS);
                } catch (InterruptedException e) {
                    Thread.currentThread().interrupt();
                    break;
                } catch (Exception e) {
                    Log.e(TAG, "Error in polling loop: " + e.getMessage());
                    try {
                        TimeUnit.SECONDS.sleep(10); // Sleep a bit before retry on error
                    } catch (InterruptedException ex) {
                        Thread.currentThread().interrupt();
                        break;
                    }
                }
            }
        });
    }

    private boolean pollNotifications() {
        SharedPreferences prefs = getSharedPreferences(AppConfig.PREFS_NAME, Context.MODE_PRIVATE);
        String token = prefs.getString(AppConfig.KEY_TOKEN, null);
        String baseUrl = prefs.getString("api_base_url", AppConfig.API_BASE_URL);
        String lastIdStr = prefs.getString(AppConfig.KEY_LAST_NOTIFICATION_ID, "0");
        int lastId = lastIdStr.isEmpty() ? 0 : Integer.parseInt(lastIdStr);

        if (token == null || token.isEmpty()) {
            Log.d(TAG, "No token, skipping poll");
            return false;
        }

        try {
            String apiUrl = baseUrl + "/notifikasi/poll?last_id=" + lastId;
            HttpURLConnection connection = createConnection(apiUrl, token);

            int responseCode = connection.getResponseCode();
            if (responseCode == HttpURLConnection.HTTP_OK) {
                String response = readStream(connection);
                JSONObject json = new JSONObject(response);
                int newLastId = json.optInt("new_last_id", lastId);
                int unreadCount = json.optInt("unread", 0);
                org.json.JSONArray items = json.optJSONArray("items");

                if (newLastId > lastId) {
                    prefs.edit().putString(AppConfig.KEY_LAST_NOTIFICATION_ID, String.valueOf(newLastId)).apply();

                    if (items != null && items.length() > 0) {
                        for (int i = 0; i < items.length(); i++) {
                            JSONObject item = items.getJSONObject(i);
                            String title = item.optString("judul", "Notifikasi Baru");
                            String message = item.optString("pesan", "");
                            String url = item.optString("url", "");
                            int id = item.optInt("id", (int) System.currentTimeMillis());

                            NotificationHelper.showNotification(this, title, message, url, id);
                        }
                    } else if (unreadCount > 0) {
                        // Fallback jika array items kosong tapi ada unread
                        NotificationHelper.showNotification(this, "Portal Sekolah", unreadCount + " notifikasi baru menanti Anda");
                    }
                }
                return true;
            } else if (responseCode == 401) {
                Log.w(TAG, "Unauthorized access, token might be invalid");
            }
            connection.disconnect();
        } catch (IOException | JSONException e) {
            Log.e(TAG, "Polling failed: " + e.getMessage());
        }
        return false;
    }

    private void pollSessionStatus() {
        SharedPreferences prefs = getSharedPreferences(AppConfig.PREFS_NAME, Context.MODE_PRIVATE);
        String token = prefs.getString(AppConfig.KEY_TOKEN, null);
        String baseUrl = prefs.getString("api_base_url", AppConfig.API_BASE_URL);

        if (token == null || token.isEmpty()) return;

        try {
            String apiUrl = baseUrl + "/session/status";
            HttpURLConnection connection = createConnection(apiUrl, token);
            int responseCode = connection.getResponseCode();
            if (responseCode == HttpURLConnection.HTTP_OK) {
                Log.d(TAG, "Session is active");
            }
            connection.disconnect();
        } catch (IOException e) {
            Log.e(TAG, "Session check failed: " + e.getMessage());
        }
    }

    private HttpURLConnection createConnection(String urlStr, String token) throws IOException {
        URL url = new URL(urlStr);
        HttpURLConnection conn = (HttpURLConnection) url.openConnection();
        conn.setRequestMethod("GET");
        conn.setRequestProperty("Authorization", "Bearer " + token);
        conn.setRequestProperty("Accept", "application/json");
        conn.setConnectTimeout(10000);
        conn.setReadTimeout(10000);
        return conn;
    }

    private String readStream(HttpURLConnection conn) throws IOException {
        BufferedReader reader = new BufferedReader(new InputStreamReader(conn.getInputStream()));
        StringBuilder sb = new StringBuilder();
        String line;
        while ((line = reader.readLine()) != null) sb.append(line);
        reader.close();
        return sb.toString();
    }

    private void acquireWakeLock() {
        PowerManager pm = (PowerManager) getSystemService(Context.POWER_SERVICE);
        if (pm != null && (wakeLock == null || !wakeLock.isHeld())) {
            wakeLock = pm.newWakeLock(PowerManager.PARTIAL_WAKE_LOCK, "AppPortal::BgWakeLock");
            wakeLock.acquire(AppConfig.WAKE_LOCK_TIMEOUT_MS);
        }
    }

    private void releaseWakeLock() {
        if (wakeLock != null && wakeLock.isHeld()) {
            wakeLock.release();
            wakeLock = null;
        }
    }

    private Notification createForegroundNotification() {
        NotificationHelper.createNotificationChannel(this);
        return new NotificationCompat.Builder(this, AppConfig.CHANNEL_ID)
                .setContentTitle("App Portal Sekolah")
                .setContentText("Layanan latar belakang aktif")
                .setSmallIcon(R.drawable.splash)
                .setPriority(NotificationCompat.PRIORITY_MIN)
                .setOngoing(true)
                .build();
    }

    public static void startService(Context context) {
        Intent intent = new Intent(context, BackgroundService.class);
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            context.startForegroundService(intent);
        } else {
            context.startService(intent);
        }
    }

    public static void stopService(Context context) {
        context.stopService(new Intent(context, BackgroundService.class));
    }

    public static void scheduleAlarm(Context context) {
        AlarmManager am = (AlarmManager) context.getSystemService(Context.ALARM_SERVICE);
        Intent intent = new Intent(context, NotificationReceiver.class);
        PendingIntent pi = PendingIntent.getBroadcast(context, 0, intent,
                PendingIntent.FLAG_UPDATE_CURRENT | PendingIntent.FLAG_IMMUTABLE);

        long triggerAt = SystemClock.elapsedRealtime() + AppConfig.POLL_INTERVAL_MS * 2; // Safeguard interval
        if (am != null) {
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                am.setAndAllowWhileIdle(AlarmManager.ELAPSED_REALTIME_WAKEUP, triggerAt, pi);
            } else {
                am.set(AlarmManager.ELAPSED_REALTIME_WAKEUP, triggerAt, pi);
            }
        }
    }

    // Static helper methods for auth data
    public static void saveToken(Context context, String token) {
        context.getSharedPreferences(AppConfig.PREFS_NAME, Context.MODE_PRIVATE)
                .edit().putString(AppConfig.KEY_TOKEN, token).apply();
    }

    public static void saveUserId(Context context, int userId) {
        context.getSharedPreferences(AppConfig.PREFS_NAME, Context.MODE_PRIVATE)
                .edit().putInt(AppConfig.KEY_USER_ID, userId).apply();
    }
}
