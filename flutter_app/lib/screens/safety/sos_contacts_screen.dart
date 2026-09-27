import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/constants/app_colors.dart';
import '../../services/sos_service.dart';

class SosContactsScreen extends StatefulWidget {
  const SosContactsScreen({super.key});

  @override
  State<SosContactsScreen> createState() => _SosContactsScreenState();
}

class _SosContactsScreenState extends State<SosContactsScreen> {
  bool _isLoading = true;
  List<Map<String, dynamic>> _contacts = [];

  @override
  void initState() {
    super.initState();
    _loadContacts();
  }

  Future<void> _loadContacts() async {
    setState(() => _isLoading = true);
    final list = await SosService.getContacts();
    if (mounted) {
      setState(() {
        _contacts = list;
        _isLoading = false;
      });
    }
  }

  /// Show permission dialog matching Reference Screenshot 3
  void _promptAddContact() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppColors.surfaceDark,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        contentPadding: const EdgeInsets.fromLTRB(24, 20, 24, 16),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 56,
              height: 56,
              decoration: BoxDecoration(
                color: const Color(0xFFF97316).withOpacity(0.15),
                borderRadius: BorderRadius.circular(16),
              ),
              child: const Icon(
                Icons.contacts_rounded,
                color: Color(0xFFF97316),
                size: 32,
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              '"RideMyCars" would like to access your Contacts.',
              style: TextStyle(
                color: AppColors.textLight,
                fontSize: 16,
                fontWeight: FontWeight.w800,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            const Text(
              'Allow contact access to select your trusted contacts for adding in emergency SOS.',
              style: TextStyle(
                color: AppColors.textMuted,
                fontSize: 12,
                height: 1.4,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
        actionsPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
        actions: [
          Row(
            children: [
              Expanded(
                child: TextButton(
                  onPressed: () {
                    Navigator.pop(ctx);
                    _openContactForm(); // Fallback to manual entry
                  },
                  style: TextButton.styleFrom(
                    foregroundColor: AppColors.textMuted,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: const Text('Don\'t Allow', style: TextStyle(fontWeight: FontWeight.bold)),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: ElevatedButton(
                  onPressed: () {
                    Navigator.pop(ctx);
                    _openContactForm();
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFF97316),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: const Text('Continue', style: TextStyle(fontWeight: FontWeight.bold)),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  void _openContactForm([Map<String, dynamic>? existing]) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _ContactFormModal(
        existingContact: existing,
        onSuccess: _loadContacts,
      ),
    );
  }

  Future<void> _deleteContact(int id, String name) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppColors.surfaceDark,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        title: const Text('Remove Contact', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold)),
        content: Text('Remove $name from your emergency contacts?', style: const TextStyle(color: AppColors.textMuted, fontSize: 13)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Cancel', style: TextStyle(color: AppColors.textMuted)),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.danger,
              foregroundColor: Colors.white,
            ),
            child: const Text('Remove'),
          ),
        ],
      ),
    );

    if (confirm == true) {
      final ok = await SosService.deleteContact(id);
      if (ok) {
        _loadContacts();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Contact removed.'), backgroundColor: AppColors.success),
          );
        }
      }
    }
  }

  Future<void> _callPhone(String phone) async {
    final uri = Uri.parse('tel:$phone');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.backgroundDark,
      appBar: AppBar(
        backgroundColor: const Color(0xFFF97316),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'SOS',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 19),
        ),
        centerTitle: true,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Colors.white),
            onPressed: _loadContacts,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFFF97316)))
          : _contacts.isEmpty
              ? _buildEmptyState() // Matching Reference Screenshot 2
              : _buildContactsList(),
      bottomNavigationBar: _contacts.isEmpty
          ? Container(
              padding: const EdgeInsets.all(16),
              decoration: const BoxDecoration(
                color: AppColors.surfaceDark,
                border: Border(top: BorderSide(color: Colors.white10)),
              ),
              child: SafeArea(
                child: ElevatedButton(
                  onPressed: _promptAddContact,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFF97316),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    elevation: 3,
                  ),
                  child: const Text(
                    'Add a Contact',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800),
                  ),
                ),
              ),
            )
          : null,
    );
  }

  /// Empty state matching Reference Screenshot 2
  Widget _buildEmptyState() {
    return Padding(
      padding: const EdgeInsets.all(24.0),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Spacer(),
          Container(
            width: 140,
            height: 140,
            decoration: BoxDecoration(
              color: const Color(0xFFF97316).withOpacity(0.08),
              shape: BoxShape.circle,
            ),
            child: const Center(
              child: Icon(
                Icons.contact_phone_rounded,
                size: 70,
                color: Color(0xFFF97316),
              ),
            ),
          ),
          const SizedBox(height: 28),
          const Text(
            'No contacts have been added..!',
            style: TextStyle(
              color: AppColors.textLight,
              fontSize: 18,
              fontWeight: FontWeight.w800,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 8),
          const Text(
            'Please add contacts to ensure your safety during all rides and trips.',
            style: TextStyle(
              color: AppColors.textMuted,
              fontSize: 13,
              height: 1.4,
            ),
            textAlign: TextAlign.center,
          ),
          const Spacer(),
        ],
      ),
    );
  }

  /// Populated list of emergency contacts
  Widget _buildContactsList() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Safety status badge
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF1E293B), Color(0xFF0F172A)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: const Color(0xFFF97316).withOpacity(0.3)),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF97316).withOpacity(0.15),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.shield_rounded, color: Color(0xFFF97316), size: 24),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'SOS Safety Shield Active',
                        style: TextStyle(color: AppColors.textLight, fontSize: 14, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '${_contacts.length} / 5 contacts registered for emergency dispatch',
                        style: const TextStyle(color: AppColors.textMuted, fontSize: 11),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // Section Title
          const Text(
            'Trusted Emergency Contacts',
            style: TextStyle(color: AppColors.textLight, fontSize: 16, fontWeight: FontWeight.w800),
          ),
          const SizedBox(height: 12),

          ListView.separated(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: _contacts.length,
            separatorBuilder: (_, __) => const SizedBox(height: 10),
            itemBuilder: (ctx, idx) {
              final c = _contacts[idx];
              final id = c['id'] as int;
              final name = c['name']?.toString() ?? 'Contact';
              final phone = c['phone']?.toString() ?? '';
              final relationship = c['relationship']?.toString() ?? 'Contact';
              final isPrimary = c['is_primary'] == true || c['is_primary'] == 1;

              return Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                decoration: BoxDecoration(
                  color: AppColors.surfaceDark,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(
                    color: isPrimary ? const Color(0xFFF97316).withOpacity(0.4) : Colors.white.withOpacity(0.06),
                    width: isPrimary ? 1.5 : 1.0,
                  ),
                ),
                child: Row(
                  children: [
                    CircleAvatar(
                      radius: 20,
                      backgroundColor: const Color(0xFFF97316).withOpacity(0.15),
                      child: Text(
                        name.isNotEmpty ? name[0].toUpperCase() : 'C',
                        style: const TextStyle(color: Color(0xFFF97316), fontWeight: FontWeight.bold, fontSize: 16),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Flexible(
                                child: Text(
                                  name,
                                  style: const TextStyle(color: AppColors.textLight, fontSize: 14, fontWeight: FontWeight.bold),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                              if (isPrimary) ...[
                                const SizedBox(width: 6),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: Colors.green.withOpacity(0.2),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: const Text('PRIMARY', style: TextStyle(color: Colors.green, fontSize: 9, fontWeight: FontWeight.bold)),
                                ),
                              ],
                            ],
                          ),
                          const SizedBox(height: 3),
                          Text(
                            '$phone • $relationship',
                            style: const TextStyle(color: AppColors.textMuted, fontSize: 12),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.phone_rounded, color: Colors.green, size: 20),
                      onPressed: () => _callPhone(phone),
                      tooltip: 'Call Contact',
                    ),
                    IconButton(
                      icon: const Icon(Icons.edit_rounded, color: AppColors.textMuted, size: 20),
                      onPressed: () => _openContactForm(c),
                      tooltip: 'Edit Contact',
                    ),
                    IconButton(
                      icon: const Icon(Icons.delete_outline_rounded, color: AppColors.danger, size: 20),
                      onPressed: () => _deleteContact(id, name),
                      tooltip: 'Remove',
                    ),
                  ],
                ),
              );
            },
          ),

          const SizedBox(height: 24),

          if (_contacts.length < 5)
            ElevatedButton.icon(
              onPressed: _promptAddContact,
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFFF97316),
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                padding: const EdgeInsets.symmetric(vertical: 14),
              ),
              icon: const Icon(Icons.person_add_alt_1_rounded),
              label: const Text('Add Another Contact', style: TextStyle(fontWeight: FontWeight.bold)),
            ),
        ],
      ),
    );
  }
}

/// Modal Sheet to Add / Edit an Emergency Contact
class _ContactFormModal extends StatefulWidget {
  final Map<String, dynamic>? existingContact;
  final VoidCallback onSuccess;

  const _ContactFormModal({
    this.existingContact,
    required this.onSuccess,
  });

  @override
  State<_ContactFormModal> createState() => _ContactFormModalState();
}

class _ContactFormModalState extends State<_ContactFormModal> {
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  String _relationship = 'Friend';
  bool _isPrimary = false;
  bool _isSubmitting = false;
  String? _errorMessage;

  final List<String> _relationshipOptions = [
    'Parent',
    'Spouse / Partner',
    'Sibling',
    'Child',
    'Friend',
    'Colleague',
    'Other',
  ];

  @override
  void initState() {
    super.initState();
    if (widget.existingContact != null) {
      _nameController.text = widget.existingContact!['name']?.toString() ?? '';
      _phoneController.text = widget.existingContact!['phone']?.toString() ?? '';
      _relationship = widget.existingContact!['relationship']?.toString() ?? 'Friend';
      if (!_relationshipOptions.contains(_relationship)) {
        _relationship = 'Other';
      }
      _isPrimary = widget.existingContact!['is_primary'] == true || widget.existingContact!['is_primary'] == 1;
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    final name = _nameController.text.trim();
    final phone = _phoneController.text.trim();

    if (name.isEmpty) {
      setState(() => _errorMessage = 'Please enter contact name.');
      return;
    }
    if (phone.isEmpty) {
      setState(() => _errorMessage = 'Please enter contact phone number.');
      return;
    }

    setState(() {
      _isSubmitting = true;
      _errorMessage = null;
    });

    Map<String, dynamic> res;
    if (widget.existingContact != null) {
      final id = widget.existingContact!['id'] as int;
      res = await SosService.updateContact(
        id,
        name: name,
        phone: phone,
        relationship: _relationship,
        isPrimary: _isPrimary,
      );
    } else {
      res = await SosService.addContact(
        name: name,
        phone: phone,
        relationship: _relationship,
        isPrimary: _isPrimary,
      );
    }

    if (!mounted) return;
    setState(() => _isSubmitting = false);

    if (res['success'] == true) {
      Navigator.pop(context);
      widget.onSuccess();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(widget.existingContact != null ? 'Contact updated.' : 'Emergency contact added.'),
          backgroundColor: AppColors.success,
        ),
      );
    } else {
      setState(() => _errorMessage = res['message'] ?? 'Could not save contact.');
    }
  }

  @override
  Widget build(BuildContext context) {
    final isEditing = widget.existingContact != null;
    return Container(
      decoration: const BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
      ),
      padding: EdgeInsets.only(
        top: 14,
        left: 20,
        right: 20,
        bottom: MediaQuery.of(context).viewInsets.bottom + 24,
      ),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Center(
              child: Container(
                width: 44,
                height: 5,
                decoration: BoxDecoration(color: Colors.white24, borderRadius: BorderRadius.circular(10)),
              ),
            ),
            const SizedBox(height: 16),
            Text(
              isEditing ? 'Edit Emergency Contact' : 'Add Emergency Contact',
              style: const TextStyle(color: AppColors.textLight, fontSize: 18, fontWeight: FontWeight.w800),
            ),
            const SizedBox(height: 18),

            // Name
            _buildField('Full Name', _nameController, 'e.g. Sarah Jenkins'),
            const SizedBox(height: 12),

            // Phone
            _buildField('Phone Number (with Country Code)', _phoneController, 'e.g. +1 555 123 4567', isPhone: true),
            const SizedBox(height: 12),

            // Relationship
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Relationship', style: TextStyle(color: AppColors.textMuted, fontSize: 11, fontWeight: FontWeight.w600)),
                const SizedBox(height: 4),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12),
                  decoration: BoxDecoration(
                    color: AppColors.backgroundDark,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.white12),
                  ),
                  child: DropdownButtonHideUnderline(
                    child: DropdownButton<String>(
                      value: _relationship,
                      isExpanded: true,
                      dropdownColor: AppColors.backgroundDark,
                      items: _relationshipOptions.map((opt) {
                        return DropdownMenuItem<String>(
                          value: opt,
                          child: Text(opt, style: const TextStyle(color: Colors.white, fontSize: 13)),
                        );
                      }).toList(),
                      onChanged: (v) {
                        if (v != null) setState(() => _relationship = v);
                      },
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            // Primary Checkbox
            Row(
              children: [
                Checkbox(
                  value: _isPrimary,
                  activeColor: const Color(0xFFF97316),
                  onChanged: (v) => setState(() => _isPrimary = v ?? false),
                ),
                const Expanded(
                  child: Text('Set as primary emergency contact', style: TextStyle(color: AppColors.textLight, fontSize: 12)),
                ),
              ],
            ),

            if (_errorMessage != null) ...[
              const SizedBox(height: 8),
              Text(_errorMessage!, style: const TextStyle(color: AppColors.danger, fontSize: 12, fontWeight: FontWeight.bold)),
            ],

            const SizedBox(height: 18),

            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: _isSubmitting ? null : () => Navigator.pop(context),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppColors.textLight,
                      side: const BorderSide(color: Colors.white24),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                    child: const Text('Cancel', style: TextStyle(fontWeight: FontWeight.bold)),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: _isSubmitting ? null : _save,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFF97316),
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                    child: _isSubmitting
                        ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                        : Text(isEditing ? 'Save Changes' : 'Add Contact', style: const TextStyle(fontWeight: FontWeight.bold)),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildField(String label, TextEditingController controller, String hint, {bool isPhone = false}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(color: AppColors.textMuted, fontSize: 11, fontWeight: FontWeight.w600)),
        const SizedBox(height: 4),
        Container(
          decoration: BoxDecoration(
            color: AppColors.backgroundDark,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.white12),
          ),
          padding: const EdgeInsets.symmetric(horizontal: 12),
          child: TextField(
            controller: controller,
            keyboardType: isPhone ? TextInputType.phone : TextInputType.text,
            style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w600),
            decoration: InputDecoration(
              hintText: hint,
              hintStyle: const TextStyle(color: AppColors.textMuted, fontSize: 12),
              border: InputBorder.none,
              isDense: true,
              contentPadding: const EdgeInsets.symmetric(vertical: 10),
            ),
          ),
        ),
      ],
    );
  }
}
