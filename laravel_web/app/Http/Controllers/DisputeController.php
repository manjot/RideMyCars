<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $disputes = Dispute::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('disputes-list', compact('disputes'));
    }

    public function create()
    {
        return view('dispute-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_type' => 'required|string|in:ride,rental,chauffeur,delivery,other',
            'booking_reference' => 'nullable|string|max:255',
            'category' => 'required|string|max:255',
            'description' => 'required|string|max:3000',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'event_completed_at' => 'nullable|date',
        ]);

        $disputeCode = 'DSP-' . date('Y') . '-' . rand(10000, 99999);
        $completedAt = !empty($validated['event_completed_at']) ? new \DateTime($validated['event_completed_at']) : now();
        $isWithin72h = Dispute::checkWithin72Hours($completedAt);

        $dispute = Dispute::create([
            'dispute_code' => $disputeCode,
            'user_id' => auth()->id(),
            'service_type' => $validated['service_type'],
            'booking_reference' => $validated['booking_reference'] ?? null,
            'category' => $validated['category'],
            'description' => $validated['description'],
            'contact_email' => $validated['contact_email'],
            'contact_phone' => $validated['contact_phone'] ?? null,
            'event_completed_at' => $completedAt,
            'deadline_at' => (clone $completedAt)->modify('+72 hours'),
            'is_within_72h' => $isWithin72h,
            'status' => 'submitted',
        ]);

        return redirect('/disputes')->with('success', "Dispute claim {$disputeCode} has been successfully filed with the Legal & Compliance Department.");
    }
}
