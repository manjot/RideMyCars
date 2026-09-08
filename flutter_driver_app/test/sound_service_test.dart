import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_driver_app/core/services/sound_service.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  setUpAll(() {
    TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
        .setMockMethodCallHandler(const MethodChannel('xyz.luan/audioplayers'), (MethodCall methodCall) async {
      return 1;
    });
    TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
        .setMockMethodCallHandler(const MethodChannel('xyz.luan/audioplayers.global'), (MethodCall methodCall) async {
      return 1;
    });
  });

  group('SoundService Tests', () {
    test('SoundService initial state is not ringing', () {
      final service = SoundService.instance;
      expect(service.isRinging, false);
      expect(service.isMuted, false);
    });

    test('SoundService starts ringing on incoming order and stops on response', () async {
      final service = SoundService.instance;

      // Start ringing for a ride order
      await service.startIncomingOrderRingtone(orderType: 'ride', orderId: 101);
      expect(service.isRinging, true);
      expect(service.isMuted, false);

      // Toggle mute without stopping
      await service.toggleMute();
      expect(service.isMuted, true);
      expect(service.isRinging, true);

      // Unmute
      await service.toggleMute();
      expect(service.isMuted, false);

      // Driver responds -> stop ringtone
      await service.stopRingtone();
      expect(service.isRinging, false);
      expect(service.isMuted, false);
    });

    test('SoundService handles delivery order ringtone cleanly', () async {
      final service = SoundService.instance;

      // Start ringing for a package delivery order
      await service.startIncomingOrderRingtone(orderType: 'package_delivery', orderId: 202);
      expect(service.isRinging, true);

      // Stop ringtone when driver responds (e.g. accepts delivery)
      await service.stopRingtone();
      expect(service.isRinging, false);
    });
  });
}
