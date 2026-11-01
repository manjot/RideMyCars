import 'dart:async';
import 'package:flutter/material.dart';
import '../core/constants/app_colors.dart';
import '../core/api/api_client.dart';

class TripChatSheet extends StatefulWidget {
  final String tripType;
  final dynamic tripId;
  final String peerName;
  final String senderRole;
  final String? senderName;

  const TripChatSheet({
    super.key,
    required this.tripType,
    required this.tripId,
    required this.peerName,
    this.senderRole = 'driver',
    this.senderName,
  });

  static Future<void> show(
    BuildContext context, {
    required String tripType,
    required dynamic tripId,
    required String peerName,
    String senderRole = 'driver',
    String? senderName,
  }) {
    return showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => TripChatSheet(
        tripType: tripType,
        tripId: tripId,
        peerName: peerName,
        senderRole: senderRole,
        senderName: senderName,
      ),
    );
  }

  @override
  State<TripChatSheet> createState() => _TripChatSheetState();
}

class _TripChatSheetState extends State<TripChatSheet> {
  final ApiClient _api = ApiClient();
  final TextEditingController _msgCtrl = TextEditingController();
  final ScrollController _scrollCtrl = ScrollController();

  List<Map<String, dynamic>> _messages = [];
  bool _loading = true;
  bool _sending = false;
  Timer? _pollTimer;

  static const List<String> _quickReplies = [
    '🚗 I am on my way!',
    '📍 I have arrived at pickup',
    '⏱️ Waiting at pickup point',
    '🙏 Thank you!',
  ];

  @override
  void initState() {
    super.initState();
    _fetchMessages();
    _pollTimer = Timer.periodic(const Duration(seconds: 4), (_) => _fetchMessages());
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    _msgCtrl.dispose();
    _scrollCtrl.dispose();
    super.dispose();
  }

  Future<void> _fetchMessages() async {
    try {
      final res = await _api.dio.get('/chat/${widget.tripType}/${widget.tripId}');
      final data = res.data;
      if (data is Map && data['messages'] is List && mounted) {
        final list = (data['messages'] as List)
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();
        setState(() {
          _messages = list;
          _loading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _sendMessage([String? presetText]) async {
    final text = (presetText ?? _msgCtrl.text).trim();
    if (text.isEmpty || _sending) return;
    if (presetText == null) _msgCtrl.clear();

    setState(() => _sending = true);
    try {
      await _api.dio.post('/chat/${widget.tripType}/${widget.tripId}', data: {
        'message': text,
        'sender_role': widget.senderRole,
        if (widget.senderName != null) 'sender_name': widget.senderName,
      });
      await _fetchMessages();
      if (_scrollCtrl.hasClients) {
        _scrollCtrl.animateTo(
          _scrollCtrl.position.maxScrollExtent + 80,
          duration: const Duration(milliseconds: 250),
          curve: Curves.easeOut,
        );
      }
    } catch (_) {
    } finally {
      if (mounted) setState(() => _sending = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final bottomInset = MediaQuery.of(context).viewInsets.bottom;
    return Padding(
      padding: EdgeInsets.only(bottom: bottomInset),
      child: Container(
        height: MediaQuery.of(context).size.height * 0.68,
        decoration: const BoxDecoration(
          color: AppColors.surfaceDark,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        child: Column(
          children: [
            // Header
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
              decoration: const BoxDecoration(
                color: AppColors.backgroundDark,
                borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
                border: Border(bottom: BorderSide(color: Colors.white10)),
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: AppColors.primary.withOpacity(0.18),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.forum_rounded, color: AppColors.primary, size: 20),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          widget.peerName,
                          style: const TextStyle(
                            color: AppColors.textLight,
                            fontWeight: FontWeight.w800,
                            fontSize: 15,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        Text(
                          'Trip #${widget.tripId} • Live In-App Chat',
                          style: const TextStyle(color: AppColors.textMuted, fontSize: 11),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close_rounded, color: AppColors.textMuted),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
            ),

            // Messages List
            Expanded(
              child: _loading
                  ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
                  : _messages.isEmpty
                      ? const Center(
                          child: Padding(
                            padding: EdgeInsets.all(24.0),
                            child: Text(
                              'No messages yet.\nSend a message or tap a quick reply below.',
                              textAlign: TextAlign.center,
                              style: TextStyle(color: AppColors.textMuted, fontSize: 13),
                            ),
                          ),
                        )
                      : ListView.builder(
                          controller: _scrollCtrl,
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                          itemCount: _messages.length,
                          itemBuilder: (_, idx) {
                            final m = _messages[idx];
                            final isMine = (m['sender_role'] ?? '').toString().toLowerCase() ==
                                widget.senderRole.toLowerCase();
                            return Align(
                              alignment: isMine ? Alignment.centerRight : Alignment.centerLeft,
                              child: Container(
                                margin: const EdgeInsets.only(bottom: 10),
                                constraints: BoxConstraints(
                                  maxWidth: MediaQuery.of(context).size.width * 0.76,
                                ),
                                padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 9),
                                decoration: BoxDecoration(
                                  color: isMine ? AppColors.primary : const Color(0xFF1E293B),
                                  borderRadius: BorderRadius.only(
                                    topLeft: const Radius.circular(16),
                                    topRight: const Radius.circular(16),
                                    bottomLeft: Radius.circular(isMine ? 16 : 4),
                                    bottomRight: Radius.circular(isMine ? 4 : 16),
                                  ),
                                ),
                                child: Column(
                                  crossAxisAlignment:
                                      isMine ? CrossAxisAlignment.end : CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      (m['message'] ?? '').toString(),
                                      style: TextStyle(
                                        color: isMine ? Colors.black : Colors.white,
                                        fontWeight: isMine ? FontWeight.w700 : FontWeight.w500,
                                        fontSize: 13.5,
                                      ),
                                    ),
                                    const SizedBox(height: 3),
                                    Text(
                                      (m['time'] ?? '').toString(),
                                      style: TextStyle(
                                        color: isMine ? Colors.black54 : AppColors.textMuted,
                                        fontSize: 10,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
            ),

            // Quick Replies
            SizedBox(
              height: 38,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 14),
                itemCount: _quickReplies.length,
                separatorBuilder: (_, __) => const SizedBox(width: 8),
                itemBuilder: (_, i) => ActionChip(
                  backgroundColor: Colors.white.withOpacity(0.06),
                  side: const BorderSide(color: Colors.white12),
                  label: Text(
                    _quickReplies[i],
                    style: const TextStyle(color: AppColors.textLight, fontSize: 11),
                  ),
                  onPressed: () => _sendMessage(_quickReplies[i]),
                ),
              ),
            ),
            const SizedBox(height: 8),

            // Input Row
            Container(
              padding: const EdgeInsets.fromLTRB(14, 10, 14, 14),
              decoration: const BoxDecoration(
                color: AppColors.backgroundDark,
                border: Border(top: BorderSide(color: Colors.white10)),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _msgCtrl,
                      style: const TextStyle(color: Colors.white, fontSize: 13.5),
                      textInputAction: TextInputAction.send,
                      onSubmitted: (_) => _sendMessage(),
                      decoration: InputDecoration(
                        hintText: 'Type a message...',
                        hintStyle: const TextStyle(color: AppColors.textMuted, fontSize: 13),
                        filled: true,
                        fillColor: AppColors.surfaceDark,
                        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(24),
                          borderSide: const BorderSide(color: Colors.white12),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(24),
                          borderSide: const BorderSide(color: Colors.white12),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  ElevatedButton(
                    onPressed: _sending ? null : () => _sendMessage(),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      foregroundColor: Colors.black,
                      shape: const CircleBorder(),
                      padding: const EdgeInsets.all(12),
                    ),
                    child: const Icon(Icons.send_rounded, size: 18),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

