import 'dart:async';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../core/api/api_client.dart';
import '../core/constants/api_constants.dart';
import '../core/services/sound_service.dart';
import '../core/services/local_notification_service.dart';

class NotificationProvider extends ChangeNotifier {
  final Dio _dio = ApiClient().dio;

  List<Map<String, dynamic>> _notifications = [];
  int _unreadCount = 0;
  Timer? _timer;
  int _lastSeenId = 0;

  List<Map<String, dynamic>> get notifications => _notifications;
  int get unreadCount => _unreadCount;

  void startPolling() {
    fetchNotifications();
    _timer?.cancel();
    _timer = Timer.periodic(const Duration(seconds: 4), (_) {
      fetchNotifications();
    });
  }

  void stopPolling() {
    _timer?.cancel();
  }


  Future<void> fetchNotifications() async {
    try {
      final res = await _dio.get(ApiConstants.notifications);
      if (res.statusCode == 200 && res.data['success'] == true) {
        final List list = res.data['notifications'] ?? [];
        final newNotifications = list.map((e) => Map<String, dynamic>.from(e)).toList();
        
        // Detect new unread notification arriving
        if (newNotifications.isNotEmpty) {
          final topId = (newNotifications.first['id'] as num?)?.toInt() ?? 0;
          if (_lastSeenId != 0 && topId > _lastSeenId) {
            _playChime();
            for (final n in newNotifications) {
              final nId = (n['id'] as num?)?.toInt() ?? 0;
              if (nId > _lastSeenId && n['is_read'] != true) {
                LocalNotificationService.instance.showNotification(
                  id: nId,
                  title: n['title'] ?? 'Driver Partner Alert',
                  body: n['message'] ?? '',
                );
              }
            }
          } else if (_lastSeenId == 0) {
            // First run: mark existing notifications as seen so we don't alert on old history
            LocalNotificationService.instance.markMultipleAsDisplayed(
              newNotifications.map((n) => (n['id'] as num?)?.toInt() ?? 0),
            );
          }
          _lastSeenId = topId;
        }

        _notifications = newNotifications;
        _unreadCount = res.data['unread_count'] ?? 0;
        notifyListeners();
      }
    } catch (e) {
      debugPrint('Error fetching notifications: $e');
    }
  }

  Future<void> sendTestNotification({String? title, String? body}) async {
    final testTitle = title ?? 'Driver Partner Alert';
    final testBody = body ?? 'Realtime notification test received successfully on your phone!';
    final id = DateTime.now().millisecondsSinceEpoch ~/ 1000;
    _playChime();
    await LocalNotificationService.instance.showNotification(
      id: id,
      title: testTitle,
      body: testBody,
      force: true,
    );
    try {
      await _dio.post('/notifications/test', data: {
        'title': testTitle,
        'message': testBody,
      });
      fetchNotifications();
    } catch (e) {
      debugPrint('Error dispatching test notification: $e');
    }
  }

  void _playChime() {
    SoundService.instance.playNotificationChime();
  }

  Future<void> markAsRead([int? id]) async {
    try {
      await _dio.post(ApiConstants.notificationsMarkRead, data: {
        if (id != null) 'id': id,
      });

      if (id != null) {
        final idx = _notifications.indexWhere((n) => n['id'] == id);
        if (idx != -1) {
          _notifications[idx]['is_read'] = true;
          if (_unreadCount > 0) _unreadCount--;
        }
      } else {
        for (var n in _notifications) {
          n['is_read'] = true;
        }
        _unreadCount = 0;
      }
      notifyListeners();
    } catch (e) {
      debugPrint('Error marking notifications as read: $e');
    }
  }

  Future<void> deleteNotification(int id) async {
    final idx = _notifications.indexWhere((n) => n['id'] == id);
    if (idx != -1) {
      final wasUnread = _notifications[idx]['is_read'] != true;
      _notifications.removeAt(idx);
      if (wasUnread && _unreadCount > 0) _unreadCount--;
      notifyListeners();
    }
    try {
      await _dio.delete('/notifications/$id');
    } catch (e) {
      debugPrint('Error deleting notification: $e');
    }
  }

  Future<void> clearAll() async {
    _notifications.clear();
    _unreadCount = 0;
    notifyListeners();
    try {
      await _dio.post('/notifications/clear');
    } catch (e) {
      debugPrint('Error clearing notifications: $e');
    }
  }

  @override
  void dispose() {
    stopPolling();
    super.dispose();
  }
}
