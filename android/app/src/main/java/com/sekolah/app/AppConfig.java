package com.sekolah.app;

public class AppConfig {
    // API base URL - adjust to your production or local IP
    public static final String API_BASE_URL = "https://app-portal-sekolah-production.up.railway.app/api";

    // Polling intervals in milliseconds
    public static final long POLL_INTERVAL_MS = 30000; // 30 seconds
    public static final long WAKE_LOCK_TIMEOUT_MS = 60000; // 60 seconds

    // Shared Preferences keys
    public static final String PREFS_NAME = "app_portal_prefs";
    public static final String KEY_TOKEN = "token";
    public static final String KEY_USER_ID = "user_id";
    public static final String KEY_LAST_NOTIFICATION_ID = "last_notification_id";
    public static final String KEY_NOTIFICATION_COUNT = "notification_count";

    // Notification channel info
    public static final String CHANNEL_ID = "portal_premium_channel_v2";
    public static final String CHANNEL_NAME = "Layanan Portal Premium";
    public static final String CHANNEL_DESCRIPTION = "Notifikasi prioritas tinggi untuk pengumuman dan tugas";

    // Notification IDs
    public static final int FOREGROUND_SERVICE_ID = 1;
    public static final int NEW_NOTIFICATION_ID = 1001;
}
