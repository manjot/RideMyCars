<?php

namespace App\Http\Controllers;

use App\Models\PrivacyRequest;
use Illuminate\Http\Request;

class PrivacyRequestController extends Controller
{
    public function index()
    {
        $requests = auth()->check()
            ? PrivacyRequest::where('user_id', auth()->id())->latest()->get()
            : collect();

        return view('privacy-requests', compact('requests'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'request_type' => 'required|string|in:access,rectification,erasure,portability,restriction,objection',
            'details' => 'required|string|max:3000',
        ]);

        $requestCode = 'PRV-' . date('Y') . '-' . rand(10000, 99999);

        $privacyRequest = PrivacyRequest::create([
            'request_code' => $requestCode,
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'request_type' => $validated['request_type'],
            'details' => $validated['details'],
            'status' => 'submitted',
        ]);

        return back()->with('success', "Your statutory privacy request ({$requestCode}) has been successfully submitted to our Data Protection Officer. We will process it within 30 statutory days.");
    }
}
