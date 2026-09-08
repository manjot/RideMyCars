import 'dart:async';
import 'package:audioplayers/audioplayers.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';

/// Centralized sound service for the Driver App.
/// Handles loud, continuous ringtones for incoming ride & package delivery orders,
/// stopping only after the driver responds (Accept / Decline) or order expires.
class SoundService {
  SoundService._internal() {
    _initAudioContext();
  }

  static final SoundService _instance = SoundService._internal();
  static SoundService get instance => _instance;

  final AudioPlayer _ringtonePlayer = AudioPlayer();
  final AudioPlayer _chimePlayer = AudioPlayer();

  final ValueNotifier<bool> isRingingNotifier = ValueNotifier<bool>(false);
  final ValueNotifier<bool> isMutedNotifier = ValueNotifier<bool>(false);

  bool get isRinging => isRingingNotifier.value;
  bool get isMuted => isMutedNotifier.value;

  Timer? _vibrateTimer;
  String? _currentOrderKey;

  void _initAudioContext() {
    try {
      final audioContext = AudioContext(
        android: const AudioContextAndroid(
          isSpeakerphoneOn: true,
          stayAwake: true,
          contentType: AndroidContentType.sonification,
          usageType: AndroidUsageType.alarm,
          audioFocus: AndroidAudioFocus.gainTransientExclusive,
        ),
        iOS: AudioContextIOS(
          category: AVAudioSessionCategory.playback,
          options: const {
            AVAudioSessionOptions.duckOthers,
          },
        ),
      );
      AudioPlayer.global.setAudioContext(audioContext).catchError((e) {
        debugPrint('SoundService global audio context error: $e');
      });
      _ringtonePlayer.setAudioContext(audioContext).catchError((_) {});
    } catch (e) {
      debugPrint('SoundService context init error: $e');
    }
  }

  /// Start playing the loud and long ringtone in a continuous loop.
  /// Rings until [stopRingtone] is called when the driver responds.
  Future<void> startIncomingOrderRingtone({String? orderType, dynamic orderId}) async {
    final newKey = '${orderType ?? "order"}_${orderId ?? "new"}';

    // If already ringing for this exact order and not muted, keep ringing
    if (isRinging && _currentOrderKey == newKey && !isMuted) {
      return;
    }

    _currentOrderKey = newKey;
    isRingingNotifier.value = true;
    isMutedNotifier.value = false;

    try {
      await _ringtonePlayer.setReleaseMode(ReleaseMode.loop);
      await _ringtonePlayer.setVolume(1.0);
      await _ringtonePlayer.play(AssetSource('audio/incoming_order_ringtone.mp3'));
    } catch (e) {
      debugPrint('SoundService ringtone play error: $e');
    }

    // Start periodic heavy haptic feedback pulses alongside the loud ringtone
    _startVibrationPulses();
  }

  /// Immediately stop the incoming order ringtone when the driver responds
  /// (Accept, Decline, Dismiss, or order expired).
  Future<void> stopRingtone() async {
    isRingingNotifier.value = false;
    isMutedNotifier.value = false;
    _currentOrderKey = null;

    _vibrateTimer?.cancel();
    _vibrateTimer = null;

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
      // Mute audio and stop vibration
      await _ringtonePlayer.setVolume(0.0);
      _vibrateTimer?.cancel();
      _vibrateTimer = null;
    } else {
      // Unmute audio back to maximum volume and resume vibration
      await _ringtonePlayer.setVolume(1.0);
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
    _ringtonePlayer.dispose();
    _chimePlayer.dispose();
  }
}
