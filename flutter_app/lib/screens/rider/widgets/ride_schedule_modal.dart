import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../../core/constants/app_colors.dart';

class ScheduleResult {
  final String scheduleType; // 'now' or 'later'
  final DateTime date;
  final TimeOfDay time;

  ScheduleResult({
    required this.scheduleType,
    required this.date,
    required this.time,
  });

  String get formattedDisplay {
    if (scheduleType == 'now') return 'Pickup now';
    return '${DateFormat('MMM d').format(date)}, ${time.format}';
  }
}

class RideScheduleModal extends StatefulWidget {
  final String initialScheduleType;
  final DateTime? initialDate;
  final TimeOfDay? initialTime;

  const RideScheduleModal({
    super.key,
    this.initialScheduleType = 'now',
    this.initialDate,
    this.initialTime,
  });

  static Future<ScheduleResult?> show(
    BuildContext context, {
    String initialScheduleType = 'now',
    DateTime? initialDate,
    TimeOfDay? initialTime,
  }) {
    return showModalBottomSheet<ScheduleResult>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => RideScheduleModal(
        initialScheduleType: initialScheduleType,
        initialDate: initialDate,
        initialTime: initialTime,
      ),
    );
  }

  @override
  State<RideScheduleModal> createState() => _RideScheduleModalState();
}

class _RideScheduleModalState extends State<RideScheduleModal> {
  late String _scheduleType;
  late DateTime _selectedDate;
  late TimeOfDay _selectedTime;

  @override
  void initState() {
    super.initState();
    _scheduleType = widget.initialScheduleType;
    _selectedDate = widget.initialDate ?? DateTime.now();
    _selectedTime = widget.initialTime ?? TimeOfDay.now();
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 30)),
      builder: (context, child) {
        return Theme(
          data: ThemeData.dark().copyWith(
            colorScheme: const ColorScheme.dark(
              primary: Color(0xFFFFDC00),
              onPrimary: Colors.black,
              surface: Color(0xFF1E293B),
              onSurface: Colors.white,
            ),
          ),
          child: child!,
        );
      },
    );
    if (picked != null) {
      setState(() => _selectedDate = picked);
    }
  }

  Future<void> _pickTime() async {
    final picked = await showTimePicker(
      context: context,
      initialTime: _selectedTime,
      builder: (context, child) {
        return Theme(
          data: ThemeData.dark().copyWith(
            colorScheme: const ColorScheme.dark(
              primary: Color(0xFFFFDC00),
              onPrimary: Colors.black,
              surface: Color(0xFF1E293B),
              onSurface: Colors.white,
            ),
          ),
          child: child!,
        );
      },
    );
    if (picked != null) {
      setState(() => _selectedTime = picked);
    }
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
                'WHEN DO YOU NEED A RIDE?',
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

          // Option 1: Pickup Now
          GestureDetector(
            onTap: () {
              Navigator.pop(
                context,
                ScheduleResult(
                  scheduleType: 'now',
                  date: DateTime.now(),
                  time: TimeOfDay.now(),
                ),
              );
            },
            child: Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: _scheduleType == 'now'
                    ? const Color(0xFF10B981).withOpacity(0.12)
                    : const Color(0xFF1E293B),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: _scheduleType == 'now'
                      ? const Color(0xFF10B981)
                      : Colors.white.withOpacity(0.08),
                  width: _scheduleType == 'now' ? 1.8 : 1,
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
                      child: Text('⚡', style: TextStyle(fontSize: 18)),
                    ),
                  ),
                  const SizedBox(width: 12),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Pickup now',
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w900,
                            fontSize: 14,
                          ),
                        ),
                        SizedBox(height: 2),
                        Text(
                          'Driver arrives in ~3–5 mins',
                          style: TextStyle(
                            color: AppColors.textMuted,
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),
                  ),
                  if (_scheduleType == 'now')
                    const Icon(Icons.check_circle_rounded, color: Color(0xFF10B981), size: 20),
                ],
              ),
            ),
          ),
          const SizedBox(height: 12),

          // Option 2: Schedule for Later
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: _scheduleType == 'later'
                  ? const Color(0xFFFFDC00).withOpacity(0.08)
                  : const Color(0xFF1E293B),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(
                color: _scheduleType == 'later'
                    ? const Color(0xFFFFDC00)
                    : Colors.white.withOpacity(0.08),
                width: _scheduleType == 'later' ? 1.8 : 1,
              ),
            ),
            child: Column(
              children: [
                GestureDetector(
                  behavior: HitTestBehavior.opaque,
                  onTap: () => setState(() => _scheduleType = 'later'),
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
                          child: Text('📅', style: TextStyle(fontSize: 18)),
                        ),
                      ),
                      const SizedBox(width: 12),
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Schedule for later',
                              style: TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.w900,
                                fontSize: 14,
                              ),
                            ),
                            SizedBox(height: 2),
                            Text(
                              'Plan up to 30 days ahead',
                              style: TextStyle(
                                color: AppColors.textMuted,
                                fontSize: 12,
                              ),
                            ),
                          ],
                        ),
                      ),
                      if (_scheduleType == 'later')
                        const Icon(Icons.check_circle_rounded, color: Color(0xFFFFDC00), size: 20),
                    ],
                  ),
                ),

                if (_scheduleType == 'later') ...[
                  const SizedBox(height: 14),
                  const Divider(color: Colors.white10, height: 1),
                  const SizedBox(height: 14),

                  Row(
                    children: [
                      // Date Selector
                      Expanded(
                        child: GestureDetector(
                          onTap: _pickDate,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                            decoration: BoxDecoration(
                              color: const Color(0xFF0F172A),
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: Colors.white24),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'DATE',
                                  style: TextStyle(
                                    color: AppColors.textMuted,
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  DateFormat('EEE, MMM d').format(_selectedDate),
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 13,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),

                      // Time Selector
                      Expanded(
                        child: GestureDetector(
                          onTap: _pickTime,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                            decoration: BoxDecoration(
                              color: const Color(0xFF0F172A),
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: Colors.white24),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'TIME',
                                  style: TextStyle(
                                    color: AppColors.textMuted,
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  _selectedTime.format(context),
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 13,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Confirm Schedule Button: Yellow with Black Text
                  SizedBox(
                    width: double.infinity,
                    height: 46,
                    child: ElevatedButton(
                      onPressed: () {
                        Navigator.pop(
                          context,
                          ScheduleResult(
                            scheduleType: 'later',
                            date: _selectedDate,
                            time: _selectedTime,
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
                        'Set Pickup Time →',
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
    );
  }
}
