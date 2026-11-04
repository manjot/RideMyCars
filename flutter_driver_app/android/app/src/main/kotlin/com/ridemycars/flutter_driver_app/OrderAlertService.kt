package com.ridemycars.flutter_driver_app

import android.app.*
import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.content.pm.ServiceInfo
import android.media.AudioAttributes
import android.media.AudioManager
import android.media.MediaPlayer
import android.os.*
import android.util.Log
import androidx.core.app.NotificationCompat
import org.json.JSONArray
import org.json.JSONObject
import java.io.BufferedReader
import java.io.InputStreamReader
import java.net.HttpURLConnection
import java.net.URL
import java.util.concurrent.Executors
import java.util.concurrent.ScheduledExecutorService
import java.util.concurrent.TimeUnit

class OrderAlertService : Service() {

    companion object {
        const val PREFS_NAME = "ridemycars_driver_bg"
        const val MONITOR_CHANNEL_ID = "ridemycars_driver_online_monitor"
        const val CALL_ALERT_CHANNEL_ID = "ridemycars_incoming_orders_v2"
        const val MONITOR_NOTIF_ID = 888001
        const val CALL_ALERT_NOTIF_ID = 999111

        const val ACTION_START = "com.ridemycars.driver.ACTION_START_MONITOR"
        const val ACTION_STOP = "com.ridemycars.driver.ACTION_STOP_MONITOR"
        const val ACTION_STOP_RINGTONE = "com.ridemycars.driver.ACTION_STOP_RINGTONE"
        const val ACTION_TEST_CALL_ALERT = "com.ridemycars.driver.ACTION_TEST_CALL_ALERT"
        const val ACTION_ALARM_POLL = "com.ridemycars.driver.ALARM_POLL_ORDERS"
        const val ACTION_MUTE_RINGTONE = "com.ridemycars.driver.ACTION_MUTE_RINGTONE"

        @Volatile
        var isRingingNative: Boolean = false
        @Volatile
        var currentRingingOrderKey: String? = null
        private val acknowledgedOrderKeys = mutableSetOf<String>()

        @Volatile
        private var staticMediaPlayer: MediaPlayer? = null
        @Volatile
        private var staticVibrator: Vibrator? = null
        @Volatile
        private var staticScreenWakeLock: PowerManager.WakeLock? = null
        private val staticMainHandler = Handler(Looper.getMainLooper())

        fun markOrderAcknowledged(key: String?) {
            if (!key.isNullOrEmpty()) {
                synchronized(acknowledgedOrderKeys) {
                    acknowledgedOrderKeys.add(key)
                }
            }
        }

        fun ensureNotificationChannels(context: Context) {
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                try {
                    val nm = context.getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager

                    val monitorChannel = NotificationChannel(
                        MONITOR_CHANNEL_ID,
                        "Driver Online Background Service",
                        NotificationManager.IMPORTANCE_LOW
                    ).apply {
                        description = "Keeps Driver App ready to receive incoming orders when closed or locked"
                        setShowBadge(false)
                    }
                    nm.createNotificationChannel(monitorChannel)

                    val soundUri = android.net.Uri.parse("android.resource://${context.packageName}/${R.raw.incoming_order_ringtone}")
                    val audioAttrs = AudioAttributes.Builder()
                        .setUsage(AudioAttributes.USAGE_ALARM)
                        .setContentType(AudioAttributes.CONTENT_TYPE_SONIFICATION)
                        .build()

                    val callChannel = NotificationChannel(
                        CALL_ALERT_CHANNEL_ID,
                        "Incoming Order Ringtone Alerts",
                        NotificationManager.IMPORTANCE_HIGH
                    ).apply {
                        description = "Loud full-screen call alerts for incoming ride, delivery & chauffeur orders"
                        enableVibration(true)
                        vibrationPattern = longArrayOf(0, 800, 400, 800, 400, 1000)
                        setSound(soundUri, audioAttrs)
                        lockscreenVisibility = Notification.VISIBILITY_PUBLIC
                        setBypassDnd(true)
                    }
                    nm.createNotificationChannel(callChannel)
                } catch (e: Exception) {
                    Log.w("OrderAlertService", "ensureNotificationChannels error: ${e.message}")
                }
            }
        }

        fun scheduleNextPollAlarm(context: Context, delayMs: Long = 5000L) {
            try {
                val prefs = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
                val isOnline = prefs.getBoolean("is_online", false)
                val isChauffeurOnline = prefs.getBoolean("is_chauffeur_online", false)
                val am = context.getSystemService(Context.ALARM_SERVICE) as AlarmManager

                val intent = Intent(context.applicationContext, OrderAlertReceiver::class.java).apply {
                    action = ACTION_ALARM_POLL
                }
                val pi = PendingIntent.getBroadcast(
                    context.applicationContext,
                    778,
                    intent,
                    PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
                )

                if (!isOnline && !isChauffeurOnline) {
                    am.cancel(pi)
                    return
                }

                val triggerAt = SystemClock.elapsedRealtime() + delayMs
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                    try {
                        am.setExactAndAllowWhileIdle(AlarmManager.ELAPSED_REALTIME_WAKEUP, triggerAt, pi)
                    } catch (_: SecurityException) {
                        am.setAndAllowWhileIdle(AlarmManager.ELAPSED_REALTIME_WAKEUP, triggerAt, pi)
                    }
                } else {
                    am.setExact(AlarmManager.ELAPSED_REALTIME_WAKEUP, triggerAt, pi)
                }
            } catch (e: Exception) {
                Log.w("OrderAlertService", "scheduleNextPollAlarm error: ${e.message}")
            }
        }

        fun checkServerAndAlertStatic(context: Context) {
            val appCtx = context.applicationContext
            val prefs = appCtx.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
            val isOnline = prefs.getBoolean("is_online", false)
            val isChauffeurOnline = prefs.getBoolean("is_chauffeur_online", false)

            if (!isOnline && !isChauffeurOnline) {
                if (isRingingNative) {
                    stopNativeRingtoneAndVibrationStatic(appCtx)
                }
                return
            }

            // Keep AlarmManager heartbeat scheduled so closed/locked state never stops polling
            scheduleNextPollAlarm(appCtx, 5000L)

            // If Flutter Activity is currently open & visible on screen, let Flutter's SoundService handle audio without echo
            if (MainActivity.isActivityForeground && isRingingNative && currentRingingOrderKey?.startsWith("test_") != true) {
                stopNativeRingtoneAndVibrationStatic(appCtx)
                return
            }

            val baseUrl = prefs.getString("base_url", "https://www.ridemycars.com/api") ?: "https://www.ridemycars.com/api"
            val token = prefs.getString("token", "") ?: ""
            val email = prefs.getString("email", "") ?: ""
            val driverId = prefs.getString("driver_id", "") ?: ""
            val countryCode = prefs.getString("country_code", "") ?: ""
            val lat = prefs.getString("lat", "") ?: ""
            val lng = prefs.getString("lng", "") ?: ""

            val query = buildString {
                if (lat.isNotEmpty() && lng.isNotEmpty()) {
                    append("?lat=$lat&lng=$lng")
                }
            }

            // 1. Poll /driver/requests
            val reqJson = fetchGetJsonStatic("$baseUrl/driver/requests$query", token, email, driverId, countryCode)
            if (reqJson != null) {
                val requestsArr: JSONArray? = reqJson.optJSONArray("requests") ?: reqJson.optJSONArray("data")
                if (requestsArr != null && requestsArr.length() > 0) {
                    for (i in 0 until requestsArr.length()) {
                        val item = requestsArr.optJSONObject(i) ?: continue
                        val rem = item.optInt("remaining_seconds", 60)
                        if (rem <= 0) continue

                        val orderType = item.optString("type", "ride")
                        val isDriverHire = orderType.contains("booking", true) ||
                            orderType.contains("hire", true) ||
                            orderType.contains("chauffeur", true) ||
                            item.optString("driver_booking_id").isNotEmpty()
                        if (isDriverHire && !isChauffeurOnline) continue
                        if (!isDriverHire && !isOnline) continue

                        val orderId = item.optString("ride_id").ifEmpty {
                            item.optString("delivery_id").ifEmpty {
                                item.optString("driver_booking_id").ifEmpty {
                                    item.optString("assignment_id").ifEmpty { item.optString("id", "new") }
                                }
                            }
                        }
                        val orderKey = "${orderType}_$orderId"
                        val isAck = synchronized(acknowledgedOrderKeys) { acknowledgedOrderKeys.contains(orderKey) }
                        if (isAck) continue

                        val pickup = item.optString("pickup_location", "Pickup Location")
                        val dropoff = item.optString("dropoff_location", "Destination")
                        val sym = item.optString("currency_symbol", "")
                        val fare = item.optString("fare", item.optString("total_price", ""))

                        val title = when {
                            isDriverHire ->
                                "🚨 New VIP Chauffeur Request! ($sym$fare)"
                            orderType.contains("delivery", true) ->
                                "🚨 New Parcel Delivery Order! ($sym$fare)"
                            else ->
                                "🚨 New Ride Order Request! ($sym$fare)"
                        }
                        val body = "Pickup: $pickup → Dropoff: $dropoff. Tap to accept!"

                        triggerIncomingOrderCallAlertStatic(
                            appCtx,
                            orderKey,
                            title,
                            body,
                            forceLaunchActivity = !MainActivity.isActivityForeground
                        )
                        return
                    }
                }
            }

            // 2. Poll /driver/pending-verifications
            val verifJson = fetchGetJsonStatic("$baseUrl/driver/pending-verifications$query", token, email, driverId, countryCode)
            if (verifJson != null) {
                val itemsArr: JSONArray? = verifJson.optJSONArray("items")
                if (itemsArr != null && itemsArr.length() > 0) {
                    for (i in 0 until itemsArr.length()) {
                        val item = itemsArr.optJSONObject(i) ?: continue
                        val orderType = item.optString("type", "verification")
                        val isDriverHire = orderType.contains("booking", true) ||
                            orderType.contains("hire", true) ||
                            orderType.contains("chauffeur", true)
                        if (isDriverHire && !isChauffeurOnline) continue
                        if (!isDriverHire && !isOnline) continue

                        val orderId = item.optString("id", "new")
                        val orderKey = "${orderType}_$orderId"
                        val isAck = synchronized(acknowledgedOrderKeys) { acknowledgedOrderKeys.contains(orderKey) }
                        if (isAck) continue

                        val pickup = item.optString("pickup", "Pickup Location")
                        val dropoff = item.optString("dropoff", "Destination")
                        val formattedAmt = item.optString("formatted_amount", "")
                        val typeLabel = item.optString("type_label", "Order")
                        val title = "🚨 New $typeLabel Request! ($formattedAmt)"
                        val body = "Pickup: $pickup → Dropoff: $dropoff. Tap to respond!"

                        triggerIncomingOrderCallAlertStatic(
                            appCtx,
                            orderKey,
                            title,
                            body,
                            forceLaunchActivity = !MainActivity.isActivityForeground
                        )
                        return
                    }
                }
            }

            // If no pending orders remain on the server (and not running a manual test alert), stop ringing
            if (isRingingNative && currentRingingOrderKey?.startsWith("test_") != true) {
                stopNativeRingtoneAndVibrationStatic(appCtx)
            }
        }

        private fun fetchGetJsonStatic(
            urlStr: String,
            token: String,
            email: String,
            driverId: String,
            countryCode: String
        ): JSONObject? {
            var conn: HttpURLConnection? = null
            return try {
                val url = URL(urlStr)
                conn = (url.openConnection() as HttpURLConnection).apply {
                    requestMethod = "GET"
                    connectTimeout = 4000
                    readTimeout = 4000
                    setRequestProperty("Accept", "application/json")
                    setRequestProperty("X-User-Role", "driver")
                    if (token.isNotEmpty()) setRequestProperty("Authorization", "Bearer $token")
                    if (email.isNotEmpty()) setRequestProperty("X-Driver-Email", email)
                    if (driverId.isNotEmpty()) setRequestProperty("X-Driver-Id", driverId)
                    if (countryCode.isNotEmpty()) {
                        setRequestProperty("X-Country", countryCode)
                        setRequestProperty("X-Country-Code", countryCode)
                    }
                }
                if (conn.responseCode == 200) {
                    val reader = BufferedReader(InputStreamReader(conn.inputStream))
                    val sb = StringBuilder()
                    var line: String?
                    while (reader.readLine().also { line = it } != null) {
                        sb.append(line)
                    }
                    reader.close()
                    JSONObject(sb.toString())
                } else {
                    null
                }
            } catch (e: Exception) {
                null
            } finally {
                conn?.disconnect()
            }
        }

        fun triggerIncomingOrderCallAlertStatic(
            context: Context,
            orderKey: String,
            title: String,
            body: String,
            forceLaunchActivity: Boolean
        ) {
            val appCtx = context.applicationContext
            staticMainHandler.post {
                try {
                    ensureNotificationChannels(appCtx)

                    // 1. Wake up screen immediately even if locked
                    acquireScreenWakeLockStatic(appCtx)

                    // 2. Start loud alarm-channel ringtone & vibration if not already ringing for this order
                    val playerPlaying = try {
                        staticMediaPlayer?.isPlaying == true
                    } catch (_: Exception) {
                        false
                    }
                    if (!isRingingNative || currentRingingOrderKey != orderKey || !playerPlaying) {
                        currentRingingOrderKey = orderKey
                        isRingingNative = true
                        startNativeRingtoneAndVibrationStatic(appCtx)
                    }

                    // 3. Post Call-Style FullScreenIntent Notification
                    postFullScreenCallNotificationStatic(appCtx, orderKey, title, body)

                    // 4. If app is closed or in background, launch MainActivity directly onto screen over lock screen
                    if (forceLaunchActivity && !MainActivity.isActivityForeground) {
                        val launchIntent = Intent(appCtx, MainActivity::class.java).apply {
                            addFlags(
                                Intent.FLAG_ACTIVITY_NEW_TASK or
                                Intent.FLAG_ACTIVITY_SINGLE_TOP or
                                Intent.FLAG_ACTIVITY_CLEAR_TOP or
                                Intent.FLAG_ACTIVITY_REORDER_TO_FRONT
                            )
                            putExtra("incoming_order_alert", true)
                            putExtra("order_key", orderKey)
                            putExtra("title", title)
                            putExtra("body", body)
                        }
                        try {
                            appCtx.startActivity(launchIntent)
                        } catch (e: Exception) {
                            Log.w("OrderAlertService", "Direct startActivity notice: ${e.message}")
                        }
                    }
                } catch (e: Exception) {
                    Log.e("OrderAlertService", "triggerIncomingOrderCallAlertStatic error: ${e.message}")
                }
            }
        }

        @Suppress("DEPRECATION")
        private fun acquireScreenWakeLockStatic(context: Context) {
            try {
                val pm = context.getSystemService(Context.POWER_SERVICE) as PowerManager
                if (staticScreenWakeLock == null) {
                    staticScreenWakeLock = pm.newWakeLock(
                        PowerManager.SCREEN_BRIGHT_WAKE_LOCK or
                        PowerManager.ACQUIRE_CAUSES_WAKEUP or
                        PowerManager.ON_AFTER_RELEASE,
                        "RideMyCarsDriver:IncomingOrderWakeLock"
                    )
                }
                if (staticScreenWakeLock?.isHeld != true) {
                    staticScreenWakeLock?.acquire(60 * 1000L)
                }
            } catch (e: Exception) {
                Log.w("OrderAlertService", "WakeLock error: ${e.message}")
            }
        }

        private fun startNativeRingtoneAndVibrationStatic(context: Context) {
            try {
                staticMediaPlayer?.stop()
                staticMediaPlayer?.release()
            } catch (_: Exception) {}

            try {
                val audioManager = context.getSystemService(Context.AUDIO_SERVICE) as AudioManager
                val maxAlarm = audioManager.getStreamMaxVolume(AudioManager.STREAM_ALARM)
                val curAlarm = audioManager.getStreamVolume(AudioManager.STREAM_ALARM)
                if (curAlarm < (maxAlarm * 0.8).toInt()) {
                    audioManager.setStreamVolume(AudioManager.STREAM_ALARM, maxAlarm, 0)
                }

                val afd = context.resources.openRawResourceFd(R.raw.incoming_order_ringtone)
                staticMediaPlayer = MediaPlayer().apply {
                    setWakeMode(context.applicationContext, PowerManager.PARTIAL_WAKE_LOCK)
                    setAudioAttributes(
                        AudioAttributes.Builder()
                            .setUsage(AudioAttributes.USAGE_ALARM)
                            .setContentType(AudioAttributes.CONTENT_TYPE_SONIFICATION)
                            .build()
                    )
                    setDataSource(afd.fileDescriptor, afd.startOffset, afd.length)
                    afd.close()
                    isLooping = true
                    setVolume(1.0f, 1.0f)
                    prepare()
                    start()
                }
            } catch (e: Exception) {
                Log.e("OrderAlertService", "MediaPlayer start error: ${e.message}")
            }

            try {
                staticVibrator = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
                    val vm = context.getSystemService(Context.VIBRATOR_MANAGER_SERVICE) as VibratorManager
                    vm.defaultVibrator
                } else {
                    @Suppress("DEPRECATION")
                    context.getSystemService(Context.VIBRATOR_SERVICE) as Vibrator
                }
                val pattern = longArrayOf(0, 800, 400, 800, 400, 1000)
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                    staticVibrator?.vibrate(VibrationEffect.createWaveform(pattern, 0))
                } else {
                    @Suppress("DEPRECATION")
                    staticVibrator?.vibrate(pattern, 0)
                }
            } catch (e: Exception) {
                Log.w("OrderAlertService", "Vibrator error: ${e.message}")
            }
        }

        private fun postFullScreenCallNotificationStatic(
            context: Context,
            orderKey: String,
            title: String,
            body: String
        ) {
            try {
                val fullScreenIntent = Intent(context, MainActivity::class.java).apply {
                    flags = Intent.FLAG_ACTIVITY_NEW_TASK or
                            Intent.FLAG_ACTIVITY_SINGLE_TOP or
                            Intent.FLAG_ACTIVITY_CLEAR_TOP
                    putExtra("incoming_order_alert", true)
                    putExtra("order_key", orderKey)
                }
                val fullScreenPendingIntent = PendingIntent.getActivity(
                    context,
                    101,
                    fullScreenIntent,
                    PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
                )

                val muteIntent = Intent(context, OrderAlertReceiver::class.java).apply {
                    action = ACTION_MUTE_RINGTONE
                    putExtra("order_key", orderKey)
                }
                val mutePendingIntent = PendingIntent.getBroadcast(
                    context,
                    102,
                    muteIntent,
                    PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
                )

                val builder = NotificationCompat.Builder(context, CALL_ALERT_CHANNEL_ID)
                    .setSmallIcon(R.mipmap.launcher_icon)
                    .setContentTitle(title)
                    .setContentText(body)
                    .setStyle(NotificationCompat.BigTextStyle().bigText(body))
                    .setPriority(NotificationCompat.PRIORITY_MAX)
                    .setCategory(NotificationCompat.CATEGORY_CALL)
                    .setVisibility(NotificationCompat.VISIBILITY_PUBLIC)
                    .setOngoing(true)
                    .setAutoCancel(true)
                    .setFullScreenIntent(fullScreenPendingIntent, true)
                    .setContentIntent(fullScreenPendingIntent)
                    .addAction(android.R.drawable.ic_lock_silent_mode_off, "Open & Respond", fullScreenPendingIntent)
                    .addAction(android.R.drawable.ic_lock_silent_mode, "Mute Ringtone", mutePendingIntent)

                val notification = builder.build()
                notification.flags = notification.flags or Notification.FLAG_INSISTENT

                val nm = context.getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
                nm.notify(CALL_ALERT_NOTIF_ID, notification)
            } catch (e: Exception) {
                Log.e("OrderAlertService", "postFullScreenCallNotificationStatic error: ${e.message}")
            }
        }

        fun stopNativeRingtoneAndVibrationStatic(context: Context) {
            isRingingNative = false
            currentRingingOrderKey = null
            try {
                staticMediaPlayer?.let {
                    if (it.isPlaying) it.stop()
                    it.release()
                }
            } catch (_: Exception) {}
            staticMediaPlayer = null

            try {
                staticVibrator?.cancel()
            } catch (_: Exception) {}

            try {
                val nm = context.getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
                nm.cancel(CALL_ALERT_NOTIF_ID)
            } catch (_: Exception) {}
        }
    }

    private var scheduler: ScheduledExecutorService? = null
    private var cpuWakeLock: PowerManager.WakeLock? = null

    override fun onCreate() {
        super.onCreate()
        ensureNotificationChannels(this)
        acquireCpuWakeLock()
        startForegroundMonitorNotification()
        scheduleNextPollAlarm(this, 5000L)
        startPollingLoop()
    }

    override fun onStartCommand(intent: Intent?, flags: Int, startId: Int): Int {
        val action = intent?.action
        Log.d("OrderAlertService", "onStartCommand action=$action")

        when (action) {
            ACTION_STOP -> {
                stopNativeRingtoneAndVibrationStatic(this)
                releaseCpuWakeLock()
                scheduleNextPollAlarm(this, 5000L)
                try {
                    if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.N) {
                        stopForeground(STOP_FOREGROUND_REMOVE)
                    } else {
                        @Suppress("DEPRECATION")
                        stopForeground(true)
                    }
                } catch (_: Exception) {}
                try {
                    val nm = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
                    nm.cancel(MONITOR_NOTIF_ID)
                } catch (_: Exception) {}
                stopSelf()
                return START_NOT_STICKY
            }
            ACTION_STOP_RINGTONE -> {
                val ackKey = intent.getStringExtra("order_key") ?: currentRingingOrderKey
                markOrderAcknowledged(ackKey)
                stopNativeRingtoneAndVibrationStatic(this)
                return START_STICKY
            }
            ACTION_TEST_CALL_ALERT -> {
                val title = intent.getStringExtra("title") ?: "🚨 Incoming Ride Order Request! (GH₵115)"
                val body = intent.getStringExtra("body") ?: "Pickup: Accra Mall → Dropoff: Kotoka Airport. Tap to accept!"
                val orderKey = intent.getStringExtra("order_key") ?: "test_order_${System.currentTimeMillis()}"
                triggerIncomingOrderCallAlertStatic(this, orderKey, title, body, forceLaunchActivity = true)
                return START_STICKY
            }
        }

        acquireCpuWakeLock()
        startForegroundMonitorNotification()
        scheduleNextPollAlarm(this, 5000L)
        startPollingLoop()
        return START_STICKY
    }

    private fun acquireCpuWakeLock() {
        try {
            val prefs = getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
            val isOnline = prefs.getBoolean("is_online", false)
            val isChauffeurOnline = prefs.getBoolean("is_chauffeur_online", false)
            if (!isOnline && !isChauffeurOnline) {
                releaseCpuWakeLock()
                return
            }
            val pm = getSystemService(Context.POWER_SERVICE) as PowerManager
            if (cpuWakeLock == null) {
                cpuWakeLock = pm.newWakeLock(
                    PowerManager.PARTIAL_WAKE_LOCK,
                    "RideMyCarsDriver:OnlinePollingCpuWakeLock"
                ).apply {
                    setReferenceCounted(false)
                }
            }
            if (cpuWakeLock?.isHeld != true) {
                cpuWakeLock?.acquire(6 * 60 * 60 * 1000L) // Hold for up to 6 hours, renewed on every poll tick
            }
        } catch (e: Exception) {
            Log.w("OrderAlertService", "acquireCpuWakeLock error: ${e.message}")
        }
    }

    private fun releaseCpuWakeLock() {
        try {
            if (cpuWakeLock?.isHeld == true) {
                cpuWakeLock?.release()
            }
        } catch (_: Exception) {}
    }

    private fun startForegroundMonitorNotification() {
        try {
            ensureNotificationChannels(this)
            val openIntent = Intent(this, MainActivity::class.java).apply {
                flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_SINGLE_TOP
            }
            val pendingIntent = PendingIntent.getActivity(
                this,
                0,
                openIntent,
                PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
            )

            val notification = NotificationCompat.Builder(this, MONITOR_CHANNEL_ID)
                .setContentTitle("RideMyCars Driver Online")
                .setContentText("Active — ready to ring for incoming orders even when closed or locked")
                .setSmallIcon(R.mipmap.launcher_icon)
                .setOngoing(true)
                .setPriority(NotificationCompat.PRIORITY_LOW)
                .setCategory(NotificationCompat.CATEGORY_SERVICE)
                .setVisibility(NotificationCompat.VISIBILITY_PUBLIC)
                .setContentIntent(pendingIntent)
                .build()

            val nm = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
            nm.notify(MONITOR_NOTIF_ID, notification)
        } catch (e: Exception) {
            Log.e("OrderAlertService", "startForegroundMonitorNotification error: ${e.message}")
        }
    }

    private fun startPollingLoop() {
        if (scheduler != null && !scheduler!!.isShutdown) return
        scheduler = Executors.newSingleThreadScheduledExecutor()
        scheduler?.scheduleWithFixedDelay({
            try {
                acquireCpuWakeLock()
                checkServerAndAlertStatic(this@OrderAlertService)
            } catch (e: Throwable) {
                Log.w("OrderAlertService", "Poll error: ${e.message}")
            }
        }, 1, 3, TimeUnit.SECONDS)
    }

    override fun onTaskRemoved(rootIntent: Intent?) {
        super.onTaskRemoved(rootIntent)
        val prefs = getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
        val isOnline = prefs.getBoolean("is_online", false)
        val isChauffeurOnline = prefs.getBoolean("is_chauffeur_online", false)
        Log.d("OrderAlertService", "onTaskRemoved: app swiped closed. isOnline=$isOnline, isChauffeurOnline=$isChauffeurOnline")

        if (isOnline || isChauffeurOnline) {
            scheduleNextPollAlarm(applicationContext, 1000L)
        }
    }

    override fun onDestroy() {
        scheduler?.shutdownNow()
        scheduler = null
        releaseCpuWakeLock()
        val prefs = getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
        val isOnline = prefs.getBoolean("is_online", false)
        val isChauffeurOnline = prefs.getBoolean("is_chauffeur_online", false)
        if (isOnline || isChauffeurOnline) {
            scheduleNextPollAlarm(applicationContext, 1500L)
        } else {
            stopNativeRingtoneAndVibrationStatic(this)
        }
        super.onDestroy()
    }

    override fun onBind(intent: Intent?): IBinder? = null
}

class OrderAlertReceiver : BroadcastReceiver() {
    override fun onReceive(context: Context, intent: Intent?) {
        val action = intent?.action
        if (action == OrderAlertService.ACTION_MUTE_RINGTONE) {
            val ackKey = intent.getStringExtra("order_key") ?: OrderAlertService.currentRingingOrderKey
            OrderAlertService.markOrderAcknowledged(ackKey)
            OrderAlertService.stopNativeRingtoneAndVibrationStatic(context)
            return
        }

        val prefs = context.getSharedPreferences(OrderAlertService.PREFS_NAME, Context.MODE_PRIVATE)
        val isOnline = prefs.getBoolean("is_online", false)
        val isChauffeurOnline = prefs.getBoolean("is_chauffeur_online", false)
        if (!isOnline && !isChauffeurOnline) return

        // 1. Schedule the next heartbeat alarm immediately
        OrderAlertService.scheduleNextPollAlarm(context, 5000L)

        // 2. Attempt to start/keep OrderAlertService alive
        val svcIntent = Intent(context, OrderAlertService::class.java).apply {
            this.action = OrderAlertService.ACTION_START
        }
        try {
            context.startService(svcIntent)
        } catch (_: Throwable) {}

        // 3. Even if the app was swiped closed and Android blocked background Service start,
        // execute a direct background server poll inside goAsync() with a PARTIAL_WAKE_LOCK!
        val pendingResult = goAsync()
        Thread {
            var wl: PowerManager.WakeLock? = null
            try {
                val pm = context.getSystemService(Context.POWER_SERVICE) as PowerManager
                wl = pm.newWakeLock(PowerManager.PARTIAL_WAKE_LOCK, "RideMyCarsDriver:ReceiverPollWakeLock")
                wl.acquire(10 * 1000L)
                OrderAlertService.checkServerAndAlertStatic(context.applicationContext)
            } catch (e: Throwable) {
                Log.w("OrderAlertReceiver", "Background poll error: ${e.message}")
            } finally {
                try {
                    if (wl?.isHeld == true) wl.release()
                } catch (_: Exception) {}
                try {
                    pendingResult.finish()
                } catch (_: Exception) {}
            }
        }.start()
    }
}
