import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/notification_provider.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<NotificationProvider>(context, listen: false).fetchNotifications();
    });
  }

  IconData _getIconForType(String type) {
    switch (type) {
      case 'ride_accepted':
      case 'ride_assignment':
        return Icons.directions_car_filled_rounded;
      case 'en_route':
        return Icons.navigation_rounded;
      case 'arrived':
        return Icons.location_on_rounded;
      case 'in_progress':
        return Icons.play_circle_fill_rounded;
      case 'completed':
        return Icons.flag_rounded;
      case 'login':
        return Icons.person_rounded;
      default:
        return Icons.notifications_rounded;
    }
  }

  Color _getColorForType(String type) {
    switch (type) {
      case 'ride_accepted': return AppColors.info;
      case 'en_route': return AppColors.info;
      case 'arrived': return AppColors.warning;
      case 'in_progress': return AppColors.success;
      case 'completed': return AppColors.primary;
      case 'login': return AppColors.purple;
      default: return AppColors.primary;
    }
  }

  Future<void> _confirmDeleteAll(BuildContext context, NotificationProvider notifs) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppColors.surfaceDark,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.delete_sweep_rounded, color: AppColors.danger, size: 24),
            SizedBox(width: 10),
            Text('Delete All', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          ],
        ),
        content: const Text(
          'Are you sure you want to delete all notifications? This cannot be undone.',
          style: TextStyle(color: AppColors.textMuted, fontSize: 14),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Cancel', style: TextStyle(color: AppColors.textMuted)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.danger,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Delete All', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );

    if (confirmed == true) {
      await notifs.clearAll();
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('All notifications deleted.')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final notifs = Provider.of<NotificationProvider>(context);

    return Scaffold(
      backgroundColor: AppColors.backgroundDark,
      appBar: AppBar(
        backgroundColor: AppColors.surfaceDark,
        elevation: 0,
        titleSpacing: 0,
        title: const Text(
          'Notifications',
          style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 18),
        ),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: AppColors.textLight, size: 18),
          onPressed: () => Navigator.pop(context),
        ),
        actions: [
          if (notifs.notifications.isNotEmpty) ...[
            if (notifs.unreadCount > 0)
              IconButton(
                icon: const Icon(Icons.done_all_rounded, color: AppColors.primary, size: 22),
                tooltip: 'Mark all read',
                onPressed: () => notifs.markAsRead(),
              ),
            IconButton(
              icon: const Icon(Icons.delete_sweep_rounded, color: AppColors.danger, size: 24),
              tooltip: 'Delete all notifications',
              onPressed: () => _confirmDeleteAll(context, notifs),
            ),
            const SizedBox(width: 4),
          ],
        ],
      ),
      body: RefreshIndicator(
        color: AppColors.primary,
        onRefresh: () => notifs.fetchNotifications(),
        child: notifs.notifications.isEmpty
            ? const Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.notifications_off_outlined, color: AppColors.textMuted, size: 54),
                    SizedBox(height: 12),
                    Text('No notifications yet', style: TextStyle(color: AppColors.textMuted, fontSize: 15)),
                  ],
                ),
              )
            : ListView.separated(
                padding: const EdgeInsets.symmetric(vertical: 8),
                itemCount: notifs.notifications.length,
                separatorBuilder: (_, __) => const Divider(color: Colors.white10, height: 1),
                itemBuilder: (context, index) {
                  final n = notifs.notifications[index];
                  final isRead = n['is_read'] == true;
                  final type = n['type'] ?? 'general';
                  final title = n['title'] ?? 'Notification';
                  final message = n['message'] ?? '';
                  final timeAgo = n['time_ago'] ?? '';
                  final notifId = n['id'] is num ? (n['id'] as num).toInt() : int.tryParse(n['id']?.toString() ?? '0') ?? 0;

                  return Dismissible(
                    key: ValueKey('rider_notif_$notifId'),
                    direction: DismissDirection.horizontal,
                    background: Container(
                      alignment: Alignment.centerLeft,
                      padding: const EdgeInsets.symmetric(horizontal: 20),
                      color: AppColors.danger,
                      child: const Row(
                        children: [
                          Icon(Icons.delete_outline_rounded, color: Colors.white, size: 24),
                          SizedBox(width: 8),
                          Text('Delete', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14)),
                        ],
                      ),
                    ),
                    secondaryBackground: Container(
                      alignment: Alignment.centerRight,
                      padding: const EdgeInsets.symmetric(horizontal: 20),
                      color: AppColors.danger,
                      child: const Row(
                        mainAxisAlignment: MainAxisAlignment.end,
                        children: [
                          Text('Delete', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14)),
                          SizedBox(width: 8),
                          Icon(Icons.delete_outline_rounded, color: Colors.white, size: 24),
                        ],
                      ),
                    ),
                    onDismissed: (direction) {
                      notifs.deleteNotification(notifId);
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text('Notification deleted'),
                          duration: Duration(seconds: 2),
                        ),
                      );
                    },
                    child: InkWell(
                      onTap: () {
                        if (!isRead) notifs.markAsRead(notifId);
                      },
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                        color: isRead ? Colors.transparent : AppColors.surfaceDark.withOpacity(0.4),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Container(
                              padding: const EdgeInsets.all(10),
                              decoration: BoxDecoration(
                                color: _getColorForType(type).withOpacity(0.18),
                                shape: BoxShape.circle,
                              ),
                              child: Icon(_getIconForType(type), color: _getColorForType(type), size: 22),
                            ),
                            const SizedBox(width: 14),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      Expanded(
                                        child: Text(
                                          title,
                                          style: TextStyle(
                                            color: AppColors.textLight,
                                            fontWeight: isRead ? FontWeight.w600 : FontWeight.w900,
                                            fontSize: 15,
                                          ),
                                        ),
                                      ),
                                      if (!isRead)
                                        Container(
                                          width: 8,
                                          height: 8,
                                          decoration: const BoxDecoration(
                                            color: AppColors.primary,
                                            shape: BoxShape.circle,
                                          ),
                                        ),
                                    ],
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    message,
                                    style: TextStyle(
                                      color: isRead ? AppColors.textMuted : AppColors.textLight.withOpacity(0.9),
                                      fontSize: 13,
                                      height: 1.35,
                                    ),
                                  ),
                                  const SizedBox(height: 6),
                                  Text(
                                    timeAgo,
                                    style: const TextStyle(
                                      color: AppColors.textMuted,
                                      fontSize: 11,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  );
                },
              ),
      ),
    );
  }
}
