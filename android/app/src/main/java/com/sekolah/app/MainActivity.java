package com.sekolah.app;

import android.os.Bundle;
import androidx.core.splashscreen.SplashScreen;
import com.getcapacitor.BridgeActivity;

public class MainActivity extends BridgeActivity {
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        SplashScreen splashScreen = SplashScreen.installSplashScreen(this);
        super.onCreate(savedInstanceState);

        // Keep the splash screen on-screen for 5 seconds
        final long startTime = System.currentTimeMillis();
        splashScreen.setKeepOnScreenCondition(() -> System.currentTimeMillis() - startTime < 5000);
    }
}
