import 'dart:async';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../core/api/api_client.dart';
import '../core/constants/api_constants.dart';

class NotificationProvider extends ChangeNotifier {
  final Dio _dio = ApiClient().dio;

  List<Map<String, dynamic>> _notifications = [];
  int _unreadCount = 0;
  Timer? _timer;

  List<Map<String, dynamic>> get notifications => _notifications;
  int get unreadCount => _unreadCount;

  void startPolling() {
    fetchNotifications();
    _timer?.cancel();
    _timer = Timer.periodic(const Duration(seconds: 10), (_) {
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
        _notifications = list.map((e) => Map<String, dynamic>.from(e)).toList();
        _unreadCount = res.data['unread_count'] ?? 0;
        notifyListeners();
      }
    } catch (e) {
      debugPrint('Error fetching notifications: $e');
    }
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

  @override
  void dispose() {
    stopPolling();
    super.dispose();
  }
}
