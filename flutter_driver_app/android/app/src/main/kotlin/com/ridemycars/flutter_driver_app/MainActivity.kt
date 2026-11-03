package com.ridemycars.flutter_driver_app

import android.content.Context
import android.content.Intent
import android.net.Uri
import android.os.Build
import android.os.Bundle
import android.os.PowerManager
import android.provider.Settings
import android.view.WindowManager
import io.flutter.embedding.android.FlutterActivity
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.plugin.common.MethodChannel

class MainActivity : FlutterActivity() {

    companion object {
        private const val CHANNEL = "com.ridemycars.driver/order_alert_service"
        @Volatile
        var isActivityForeground: Boolean = false
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableLockScreenWakeUp()
        ensureBackgroundServiceIfOnline()
    }

    override fun onNewIntent(intent: Intent) {
        super.onNewIntent(intent)
        enableLockScreenWakeUp()
    }

    override fun onResume() {
        super.onResume()
        isActivityForeground = true
        enableLockScreenWakeUp()
    }

    override fun onPause() {
        isActivityForeground = false
        ensureBackgroundServiceIfOnline()
        super.onPause()
    }

    override fun onStop() {
        isActivityForeground = false
        ensureBackgroundServiceIfOnline()
        super.onStop()
    }

    override fun onDestroy() {
        isActivityForeground = false
        ensureBackgroundServiceIfOnline()
        super.onDestroy()
    }

    private fun ensureBackgroundServiceIfOnline() {
        try {
            val prefs = getSharedPreferences(OrderAlertService.PREFS_NAME, Context.MODE_PRIVATE)
            val isOnline = prefs.getBoolean("is_online", false)
            val isChauffeurOnline = prefs.getBoolean("is_chauffeur_online", false)
            if (isOnline || isChauffeurOnline) {
                OrderAlertService.scheduleNextPollAlarm(this, 3000L)
                val svcIntent = Intent(this, OrderAlertService::class.java).apply {
                    action = OrderAlertService.ACTION_START
                }
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                    startForegroundService(svcIntent)
                } else {
                    startService(svcIntent)
                }
            }
        } catch (_: Throwable) {}
    }

    private fun requestBatteryOptimizationExemptionIfNeeded() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            try {
                val prefs = getSharedPreferences(OrderAlertService.PREFS_NAME, Context.MODE_PRIVATE)
                val alreadyPrompted = prefs.getBoolean("battery_opt_prompted", false)
                val pm = getSystemService(Context.POWER_SERVICE) as PowerManager
                if (!pm.isIgnoringBatteryOptimizations(packageName) && !alreadyPrompted) {
                    prefs.edit().putBoolean("battery_opt_prompted", true).apply()
                    val intent = Intent(Settings.ACTION_REQUEST_IGNORE_BATTERY_OPTIMIZATIONS).apply {
                        data = Uri.parse("package:$packageName")
                    }
                    startActivity(intent)
                }
            } catch (_: Throwable) {}
        }
    }

    @Suppress("DEPRECATION")
    private fun enableLockScreenWakeUp() {
        try {
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O_MR1) {
                setShowWhenLocked(true)
                setTurnScreenOn(true)
            } else {
                window.addFlags(
                    WindowManager.LayoutParams.FLAG_SHOW_WHEN_LOCKED or
                    WindowManager.LayoutParams.FLAG_TURN_SCREEN_ON
                )
            }
            window.addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON)
        } catch (_: Exception) {}
    }

    override fun configureFlutterEngine(flutterEngine: FlutterEngine) {
        super.configureFlutterEngine(flutterEngine)

        MethodChannel(flutterEngine.dartExecutor.binaryMessenger, CHANNEL)
            .setMethodCallHandler { call, result ->
                val prefs = getSharedPreferences(OrderAlertService.PREFS_NAME, Context.MODE_PRIVATE)
                when (call.method) {
                    "startService" -> {
                        val token = call.argument<String>("token") ?: ""
                        val email = call.argument<String>("email") ?: ""
                        val driverId = call.argument<String>("driverId") ?: ""
                        val countryCode = call.argument<String>("countryCode") ?: ""
                        val baseUrl = call.argument<String>("baseUrl") ?: "https://www.ridemycars.com/api"
                        val isOnline = call.argument<Boolean>("isOnline") ?: false
                        val isChauffeurOnline = call.argument<Boolean>("isChauffeurOnline") ?: false
                        val lat = call.argument<String>("lat") ?: ""
                        val lng = call.argument<String>("lng") ?: ""

                        prefs.edit()
                            .putString("token", token)
                            .putString("email", email)
                            .putString("driver_id", driverId)
                            .putString("country_code", countryCode)
                            .putString("baseUrl", baseUrl)
                            .putString("base_url", baseUrl)
                            .putBoolean("is_online", isOnline)
                            .putBoolean("is_chauffeur_online", isChauffeurOnline)
                            .apply {
                                if (lat.isNotEmpty()) putString("lat", lat)
                                if (lng.isNotEmpty()) putString("lng", lng)
                            }
                            .commit()

                        requestBatteryOptimizationExemptionIfNeeded()
                        OrderAlertService.scheduleNextPollAlarm(this, 3000L)

                        val svcIntent = Intent(this, OrderAlertService::class.java).apply {
                            action = OrderAlertService.ACTION_START
                        }
                        try {
                            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                                startForegroundService(svcIntent)
                            } else {
                                startService(svcIntent)
                            }
                        } catch (_: Throwable) {
                            try {
                                startService(svcIntent)
                            } catch (_: Throwable) {}
                        }
                        result.success(true)
                    }
                    "updateLocation" -> {
                        val lat = call.argument<String>("lat") ?: ""
                        val lng = call.argument<String>("lng") ?: ""
                        prefs.edit()
                            .putString("lat", lat)
                            .putString("lng", lng)
                            .apply()
                        result.success(true)
                    }
                    "stopRingtone" -> {
                        val orderKey = call.argument<String>("orderKey") ?: OrderAlertService.currentRingingOrderKey
                        OrderAlertService.markOrderAcknowledged(orderKey)
                        OrderAlertService.stopNativeRingtoneAndVibrationStatic(this)
                        result.success(true)
                    }
                    "stopService" -> {
                        prefs.edit()
                            .putBoolean("is_online", false)
                            .putBoolean("is_chauffeur_online", false)
                            .commit()
                        OrderAlertService.scheduleNextPollAlarm(this, 5000L)
                        OrderAlertService.stopNativeRingtoneAndVibrationStatic(this)
                        val svcIntent = Intent(this, OrderAlertService::class.java).apply {
                            action = OrderAlertService.ACTION_STOP
                        }
                        try {
                            startService(svcIntent)
                        } catch (_: Throwable) {
                            try {
                                stopService(Intent(this, OrderAlertService::class.java))
                            } catch (_: Throwable) {}
                        }
                        result.success(true)
                    }
                    else -> result.notImplemented()
                }
            }
    }
}
