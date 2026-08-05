import 'package:flutter/material.dart';

class HireScreen extends StatelessWidget {
  const HireScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Hire a Driver')),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.person_search, size: 80, color: Color(0xFFF97316)),
            const SizedBox(height: 16),
            const Text('Hire Driver UI', style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            const Text('List of available drivers for hire will go here.'),
          ],
        ),
      ),
    );
  }
}
