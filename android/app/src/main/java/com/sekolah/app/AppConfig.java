package com.sekolah.app;

public class AppConfig {
    // API base URL - adjust to your production or local IP
    public static final String API_BASE_URL = "https://app-portal-sekolah-production.up.railway.app/api";

    // Polling intervals in milliseconds
    // Polling is only a fallback when push notifications are unavailable.  Polling every
    // 30 seconds keeps a foreground service awake and noticeably drains the battery.
    public static final long POLL_INTERVAL_MS = 15 * 60 * 1000; // 15 minutes
    public static final long WAKE_LOCK_TIMEOUT_MS = 60000; // 60 seconds

    // Shared Preferences keys
    public static final String PREFS_NAME = "app_portal_prefs";
    public static final String KEY_TOKEN = "token";
    public static final String KEY_FCM_TOKEN = "fcm_token";
    public static final String KEY_USER_ID = "user_id";
    public static final String KEY_LAST_NOTIFICATION_ID = "last_notification_id";
    public static final String KEY_NOTIFICATION_INITIALIZED = "notification_cursor_initialized";
    public static final String KEY_NOTIFICATION_COUNT = "notification_count";

    // Notification channel info
    public static final String CHANNEL_ID = "portal_alerts_v4_pop";
    public static final String CHANNEL_NAME = "Notifikasi Penting";
    public static final String CHANNEL_DESCRIPTION = "Channel untuk notifikasi melayang (Heads-up)";
    public static final String SERVICE_CHANNEL_ID = "portal_background_v1";
    public static final String SERVICE_CHANNEL_NAME = "Status sinkronisasi";

    // Notification IDs
    public static final int FOREGROUND_SERVICE_ID = 1;
    public static final int NEW_NOTIFICATION_ID = 1001;
}
