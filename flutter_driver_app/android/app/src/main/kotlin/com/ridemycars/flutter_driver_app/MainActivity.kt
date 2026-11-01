package com.ridemycars.flutter_driver_app

import android.content.Context
import android.content.Intent
import android.os.Build
import android.os.Bundle
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
        super.onPause()
    }

    override fun onDestroy() {
        isActivityForeground = false
        super.onDestroy()
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
                            .apply()

                        val svcIntent = Intent(this, OrderAlertService::class.java).apply {
                            action = OrderAlertService.ACTION_START
                        }
                        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                            startForegroundService(svcIntent)
                        } else {
                            startService(svcIntent)
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
                        val orderKey = call.argument<String>("orderKey")
                        val svcIntent = Intent(this, OrderAlertService::class.java).apply {
                            action = OrderAlertService.ACTION_STOP_RINGTONE
                            if (orderKey != null) putExtra("order_key", orderKey)
                        }
                        startService(svcIntent)
                        result.success(true)
                    }
                    "stopService" -> {
                        prefs.edit()
                            .putBoolean("is_online", false)
                            .putBoolean("is_chauffeur_online", false)
                            .apply()
                        val svcIntent = Intent(this, OrderAlertService::class.java).apply {
                            action = OrderAlertService.ACTION_STOP
                        }
                        startService(svcIntent)
                        result.success(true)
                    }
                    else -> result.notImplemented()
                }
            }
    }
}
