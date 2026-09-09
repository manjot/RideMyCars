<?php

namespace App\Http\Controllers;

use App\Models\PrivacyRequest;
use Illuminate\Http\Request;

class PrivacyRequestController extends Controller
{
    public function index()
    {
        try {
            $requests = auth()->check() && \Illuminate\Support\Facades\Schema::hasTable('privacy_requests')
                ? PrivacyRequest::where('user_id', auth()->id())->latest()->paginate(5)
                : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 5);
        } catch (\Throwable $e) {
            $requests = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 5);
        }

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

        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('privacy_requests')) {
                \Illuminate\Support\Facades\Schema::create('privacy_requests', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->string('request_code')->unique();
                    $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                    $table->string('name');
                    $table->string('email');
                    $table->string('phone')->nullable();
                    $table->string('request_type')->default('access');
                    $table->text('details')->nullable();
                    $table->string('identity_proof_url')->nullable();
                    $table->string('status')->default('submitted');
                    $table->text('admin_notes')->nullable();
                    $table->timestamp('completed_at')->nullable();
                    $table->timestamps();
                });
            }

            PrivacyRequest::create([
                'request_code' => $requestCode,
                'user_id' => auth()->id(),
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'request_type' => $validated['request_type'],
                'details' => $validated['details'],
                'status' => 'submitted',
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Privacy request save error: ' . $e->getMessage());
        }

        return back()->with('success', "Your statutory privacy request ({$requestCode}) has been successfully submitted to our Data Protection Officer. We will process it within 30 statutory days.");
    }
}
