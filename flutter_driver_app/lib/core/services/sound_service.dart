import 'dart:async';
import 'package:audioplayers/audioplayers.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';
import 'local_notification_service.dart';

/// Centralized sound service for the Driver App.
/// Handles loud, continuous ringtones for incoming ride, chauffeur & package delivery orders,
/// including when the app is in the background or the phone lock screen is on.
class SoundService {
  SoundService._internal() {
    _initAudioContext();
  }

  static final SoundService _instance = SoundService._internal();
  static SoundService get instance => _instance;

  final AudioPlayer _ringtonePlayer = AudioPlayer();
  final AudioPlayer _chimePlayer = AudioPlayer();
  final AudioPlayer _keepAlivePlayer = AudioPlayer();

  final ValueNotifier<bool> isRingingNotifier = ValueNotifier<bool>(false);
  final ValueNotifier<bool> isMutedNotifier = ValueNotifier<bool>(false);

  bool get isRinging => isRingingNotifier.value;
  bool get isMuted => isMutedNotifier.value;

  Timer? _vibrateTimer;
  Timer? _ringWatchdogTimer;
  String? _currentOrderKey;
  String? _lastAlertTitle;
  String? _lastAlertBody;
  bool _keepAliveActive = false;

  void _initAudioContext() {
    try {
      final ringtoneContext = AudioContext(
        android: const AudioContextAndroid(
          isSpeakerphoneOn: true,
          stayAwake: true,
          contentType: AndroidContentType.sonification,
          usageType: AndroidUsageType.alarm,
          audioFocus: AndroidAudioFocus.gainTransientMayDuck,
        ),
        iOS: AudioContextIOS(
          category: AVAudioSessionCategory.playback,
          options: const {
            AVAudioSessionOptions.mixWithOthers,
            AVAudioSessionOptions.duckOthers,
          },
        ),
      );
      AudioPlayer.global.setAudioContext(ringtoneContext).catchError((e) {
        debugPrint('SoundService global audio context error: $e');
      });
      _ringtonePlayer.setAudioContext(ringtoneContext).catchError((_) {});

      final keepAliveContext = AudioContext(
        android: const AudioContextAndroid(
          isSpeakerphoneOn: false,
          stayAwake: true,
          contentType: AndroidContentType.music,
          usageType: AndroidUsageType.media,
          audioFocus: AndroidAudioFocus.none,
        ),
        iOS: AudioContextIOS(
          category: AVAudioSessionCategory.playback,
          options: const {
            AVAudioSessionOptions.mixWithOthers,
          },
        ),
      );
      _keepAlivePlayer.setAudioContext(keepAliveContext).catchError((_) {});
    } catch (e) {
      debugPrint('SoundService context init error: $e');
    }
  }

  /// Keeps the background audio session and Dart isolate alive while the driver is online
  /// so that incoming orders are received and ring immediately even when the phone is locked.
  Future<void> setOnlineKeepAlive(bool online) async {
    if (online == _keepAliveActive) return;
    _keepAliveActive = online;
    try {
      if (online) {
        await _keepAlivePlayer.setReleaseMode(ReleaseMode.loop);
        await _keepAlivePlayer.setVolume(0.0001);
        await _keepAlivePlayer.play(AssetSource('audio/notification.mp3'));
      } else {
        await _keepAlivePlayer.stop();
      }
    } catch (e) {
      debugPrint('SoundService keepAlive notice: $e');
    }
  }

  /// Start playing the loud and long ringtone in a continuous loop,
  /// and trigger the system lock-screen / full-screen alert notification.
  /// Rings until [stopRingtone] is called when the driver responds.
  Future<void> startIncomingOrderRingtone({
    String? orderType,
    dynamic orderId,
    String? title,
    String? body,
  }) async {
    final newKey = '${orderType ?? "order"}_${orderId ?? "new"}';

    // If already ringing (or user intentionally muted) for this exact order, keep state
    if (isRinging && _currentOrderKey == newKey) {
      return;
    }

    _currentOrderKey = newKey;
    isRingingNotifier.value = true;
    isMutedNotifier.value = false;

    final alertTitle = title ?? _formatOrderAlertTitle(orderType);
    final alertBody = body ?? 'New order request waiting! Tap to open and accept.';
    _lastAlertTitle = alertTitle;
    _lastAlertBody = alertBody;

    // Fire high-priority system lock-screen notification with insistent ringtone
    LocalNotificationService.instance.showIncomingOrderAlert(
      title: alertTitle,
      body: alertBody,
      orderKey: newKey,
    );

    await _playLoudRingtoneLoop();

    // Start periodic heavy haptic feedback pulses alongside the loud ringtone
    _startVibrationPulses();

    // Start watchdog timer to ensure audio keeps ringing even across lock-screen transitions
    _startRingWatchdog();
  }

  String _formatOrderAlertTitle(String? orderType) {
    final t = (orderType ?? '').toLowerCase();
    if (t.contains('hire') || t.contains('chauffeur') || t.contains('booking')) {
      return '🚨 New VIP Chauffeur Request!';
    }
    if (t.contains('delivery') || t.contains('package') || t.contains('parcel')) {
      return '🚨 New Parcel Delivery Order!';
    }
    return '🚨 New Ride Order Request!';
  }

  Future<void> _playLoudRingtoneLoop() async {
    try {
      await _ringtonePlayer.setReleaseMode(ReleaseMode.loop);
      await _ringtonePlayer.setVolume(isMuted ? 0.0 : 1.0);
      await _ringtonePlayer.play(AssetSource('audio/incoming_order_ringtone.mp3'));
    } catch (e) {
      debugPrint('SoundService ringtone play error: $e');
      try {
        await _ringtonePlayer.play(AssetSource('audio/incoming_order_ringtone.wav'));
      } catch (_) {}
    }
  }

  void _startRingWatchdog() {
    _ringWatchdogTimer?.cancel();
    int tickCount = 0;
    _ringWatchdogTimer = Timer.periodic(const Duration(seconds: 4), (_) async {
      if (!isRinging || isMuted) return;
      tickCount++;
      try {
        if (_ringtonePlayer.state != PlayerState.playing) {
          await _playLoudRingtoneLoop();
        }
        // Re-post lock-screen alert every 8 seconds while ringing so iOS/Android lock screen keeps ringing
        if (tickCount % 2 == 0 && _currentOrderKey != null) {
          LocalNotificationService.instance.showIncomingOrderAlert(
            title: _lastAlertTitle ?? '🚨 Incoming Order Request!',
            body: _lastAlertBody ?? 'Tap to view and accept the order.',
            orderKey: _currentOrderKey,
            forceRefresh: true,
          );
        }
      } catch (_) {}
    });
  }

  static const MethodChannel _nativeChannel =
      MethodChannel('com.ridemycars.driver/order_alert_service');

  /// Immediately stop the incoming order ringtone and clear the lock-screen alert
  /// when the driver responds (Accept, Decline, Dismiss, or order expired).
  Future<void> stopRingtone() async {
    final prevKey = _currentOrderKey;
    isRingingNotifier.value = false;
    isMutedNotifier.value = false;
    _currentOrderKey = null;

    _vibrateTimer?.cancel();
    _vibrateTimer = null;
    _ringWatchdogTimer?.cancel();
    _ringWatchdogTimer = null;

    LocalNotificationService.instance.cancelIncomingOrderAlert();
    try {
      await _nativeChannel.invokeMethod('stopRingtone', {'orderKey': prevKey});
    } catch (_) {}

    try {
      await _ringtonePlayer.stop();
    } catch (e) {
      debugPrint('SoundService stop error: $e');
    }
  }

  /// Toggle mute for the active order without rejecting the job.
  Future<void> toggleMute() async {
    if (!isRinging) return;

    final newMuteState = !isMuted;
    isMutedNotifier.value = newMuteState;

    if (newMuteState) {
      // Mute audio, cancel system ringtone notification, and stop vibration
      await _ringtonePlayer.setVolume(0.0);
      _vibrateTimer?.cancel();
      _vibrateTimer = null;
      LocalNotificationService.instance.cancelIncomingOrderAlert();
      try {
        await _nativeChannel.invokeMethod('stopRingtone', {'orderKey': _currentOrderKey});
      } catch (_) {}
    } else {
      // Unmute audio back to maximum volume and resume vibration
      await _ringtonePlayer.setVolume(1.0);
      if (_ringtonePlayer.state != PlayerState.playing) {
        await _playLoudRingtoneLoop();
      }
      _startVibrationPulses();
    }
  }

  void _startVibrationPulses() {
    _vibrateTimer?.cancel();
    // Pulse immediately
    HapticFeedback.heavyImpact().catchError((_) {});

    // And repeat every 1.5 seconds while ringing
    _vibrateTimer = Timer.periodic(const Duration(milliseconds: 1500), (_) {
      if (!isRinging || isMuted) {
        _vibrateTimer?.cancel();
        return;
      }
      HapticFeedback.heavyImpact().catchError((_) {});
    });
  }

  /// Play a one-shot notification chime (for general notifications / updates).
  Future<void> playNotificationChime() async {
    try {
      await _chimePlayer.setVolume(0.9);
      await _chimePlayer.play(AssetSource('audio/notification.mp3'));
    } catch (e) {
      debugPrint('SoundService chime error: $e');
    }
  }

  void dispose() {
    _vibrateTimer?.cancel();
    _ringWatchdogTimer?.cancel();
    _ringtonePlayer.dispose();
    _chimePlayer.dispose();
    _keepAlivePlayer.dispose();
  }
}
