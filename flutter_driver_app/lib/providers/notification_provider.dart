import 'dart:async';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../core/api/api_client.dart';
import '../core/constants/api_constants.dart';
import '../core/services/sound_service.dart';

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

  Future<void> clearAll() async {
    try {
      await _dio.post('/notifications/clear');
      _notifications.clear();
      _unreadCount = 0;
      notifyListeners();
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
