import 'package:flutter/material.dart';
import '../../../core/constants/app_colors.dart';

class PassengerResult {
  final String riderType; // 'me' or 'someone_else'
  final String? name;
  final String? phone;

  PassengerResult({
    required this.riderType,
    this.name,
    this.phone,
  });

  String get formattedDisplay {
    if (riderType == 'me' || name == null || name!.isEmpty) return 'For me';
    return 'For ${name!.split(' ').first}';
  }
}

class RidePassengerModal extends StatefulWidget {
  final String initialRiderType;
  final String? initialName;
  final String? initialPhone;
  final String? currentUserName;

  const RidePassengerModal({
    super.key,
    this.initialRiderType = 'me',
    this.initialName,
    this.initialPhone,
    this.currentUserName,
  });

  static Future<PassengerResult?> show(
    BuildContext context, {
    String initialRiderType = 'me',
    String? initialName,
    String? initialPhone,
    String? currentUserName,
  }) {
    return showModalBottomSheet<PassengerResult>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => RidePassengerModal(
        initialRiderType: initialRiderType,
        initialName: initialName,
        initialPhone: initialPhone,
        currentUserName: currentUserName,
      ),
    );
  }

  @override
  State<RidePassengerModal> createState() => _RidePassengerModalState();
}

class _RidePassengerModalState extends State<RidePassengerModal> {
  late String _riderType;
  late TextEditingController _nameController;
  late TextEditingController _phoneController;

  @override
  void initState() {
    super.initState();
    _riderType = widget.initialRiderType;
    _nameController = TextEditingController(text: widget.initialName ?? '');
    _phoneController = TextEditingController(text: widget.initialPhone ?? '');
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.only(
        bottom: MediaQuery.of(context).viewInsets.bottom + 20,
        top: 18,
        left: 20,
        right: 20,
      ),
      decoration: const BoxDecoration(
        color: Color(0xFF0F172A),
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
        boxShadow: [
          BoxShadow(
            color: Colors.black54,
            blurRadius: 30,
            offset: Offset(0, -6),
          ),
        ],
      ),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Drag handle
            Center(
              child: Container(
                width: 44,
                height: 4,
                margin: const EdgeInsets.only(bottom: 14),
                decoration: BoxDecoration(
                  color: Colors.white24,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),

            // Header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'WHO IS RIDING?',
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w900,
                    fontSize: 13,
                    letterSpacing: 0.5,
                  ),
                ),
                GestureDetector(
                  onTap: () => Navigator.pop(context),
                  child: Container(
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.08),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.close, color: Colors.white70, size: 16),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Option 1: For Me
            GestureDetector(
              onTap: () {
                Navigator.pop(
                  context,
                  PassengerResult(riderType: 'me'),
                );
              },
              child: Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: _riderType == 'me'
                      ? const Color(0xFF10B981).withOpacity(0.12)
                      : const Color(0xFF1E293B),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(
                    color: _riderType == 'me'
                        ? const Color(0xFF10B981)
                        : Colors.white.withOpacity(0.08),
                    width: _riderType == 'me' ? 1.8 : 1,
                  ),
                ),
                child: Row(
                  children: [
                    Container(
                      width: 38,
                      height: 38,
                      decoration: BoxDecoration(
                        color: const Color(0xFF10B981).withOpacity(0.18),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Center(
                        child: Text('👤', style: TextStyle(fontSize: 18)),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'For me',
                            style: TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.w900,
                              fontSize: 14,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            widget.currentUserName != null && widget.currentUserName!.isNotEmpty
                                ? '${widget.currentUserName} (You)'
                                : 'Account owner (You)',
                            style: const TextStyle(
                              color: AppColors.textMuted,
                              fontSize: 12,
                            ),
                          ),
                        ],
                      ),
                    ),
                    if (_riderType == 'me')
                      const Icon(Icons.check_circle_rounded, color: Color(0xFF10B981), size: 20),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 12),

            // Option 2: Someone Else
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: _riderType == 'someone_else'
                    ? const Color(0xFFFFDC00).withOpacity(0.08)
                    : const Color(0xFF1E293B),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: _riderType == 'someone_else'
                      ? const Color(0xFFFFDC00)
                      : Colors.white.withOpacity(0.08),
                  width: _riderType == 'someone_else' ? 1.8 : 1,
                ),
              ),
              child: Column(
                children: [
                  GestureDetector(
                    behavior: HitTestBehavior.opaque,
                    onTap: () => setState(() => _riderType = 'someone_else'),
                    child: Row(
                      children: [
                        Container(
                          width: 38,
                          height: 38,
                          decoration: BoxDecoration(
                            color: const Color(0xFFFFDC00).withOpacity(0.18),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Center(
                            child: Text('👥', style: TextStyle(fontSize: 18)),
                          ),
                        ),
                        const SizedBox(width: 12),
                        const Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Someone else',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w900,
                                  fontSize: 14,
                                ),
                              ),
                              SizedBox(height: 2),
                              Text(
                                'Driver contacts the passenger directly',
                                style: TextStyle(
                                  color: AppColors.textMuted,
                                  fontSize: 12,
                                ),
                              ),
                            ],
                          ),
                        ),
                        if (_riderType == 'someone_else')
                          const Icon(Icons.check_circle_rounded, color: Color(0xFFFFDC00), size: 20),
                      ],
                    ),
                  ),

                  if (_riderType == 'someone_else') ...[
                    const SizedBox(height: 14),
                    const Divider(color: Colors.white10, height: 1),
                    const SizedBox(height: 14),

                    // Rider Full Name Input
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'RIDER FULL NAME *',
                          style: TextStyle(
                            color: AppColors.textMuted,
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            letterSpacing: 0.5,
                          ),
                        ),
                        const SizedBox(height: 6),
                        TextField(
                          controller: _nameController,
                          style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold),
                          decoration: InputDecoration(
                            hintText: 'e.g. Sarah Jenkins',
                            hintStyle: const TextStyle(color: Colors.white38, fontSize: 12),
                            filled: true,
                            fillColor: const Color(0xFF0F172A),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: const BorderSide(color: Colors.white24),
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: const BorderSide(color: Colors.white24),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: const BorderSide(color: Color(0xFFFFDC00), width: 1.5),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    // Rider Phone Number Input
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'RIDER PHONE NUMBER *',
                          style: TextStyle(
                            color: AppColors.textMuted,
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            letterSpacing: 0.5,
                          ),
                        ),
                        const SizedBox(height: 6),
                        TextField(
                          controller: _phoneController,
                          keyboardType: TextInputType.phone,
                          style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold),
                          decoration: InputDecoration(
                            hintText: 'e.g. +1 555-0199',
                            hintStyle: const TextStyle(color: Colors.white38, fontSize: 12),
                            filled: true,
                            fillColor: const Color(0xFF0F172A),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: const BorderSide(color: Colors.white24),
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: const BorderSide(color: Colors.white24),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: const BorderSide(color: Color(0xFFFFDC00), width: 1.5),
                            ),
                          ),
                        ),
                        const SizedBox(height: 4),
                        const Text(
                          "We'll send driver arrival alerts via SMS to this phone.",
                          style: TextStyle(color: Colors.white38, fontSize: 10.5),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // Save Passenger Details Button: Yellow with Black Text
                    SizedBox(
                      width: double.infinity,
                      height: 46,
                      child: ElevatedButton(
                        onPressed: () {
                          if (_nameController.text.trim().isEmpty) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Please enter rider full name')),
                            );
                            return;
                          }
                          Navigator.pop(
                            context,
                            PassengerResult(
                              riderType: 'someone_else',
                              name: _nameController.text.trim(),
                              phone: _phoneController.text.trim(),
                            ),
                          );
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFFFFDC00),
                          foregroundColor: Colors.black,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          elevation: 3,
                        ),
                        child: const Text(
                          'Save Passenger Details ✓',
                          style: TextStyle(
                            color: Colors.black,
                            fontWeight: FontWeight.w900,
                            fontSize: 14,
                          ),
                        ),
                      ),
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(height: 10),
          ],
        ),
      ),
    );
  }
}
