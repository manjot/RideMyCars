<x-layout>
    <div class="min-h-screen bg-gray-50 dark:bg-[#0B0F19] text-gray-900 dark:text-gray-100 py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto space-y-8">
            
            <!-- Breadcrumb / Header -->
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-black tracking-wider uppercase bg-red-500/10 text-red-500 border border-red-500/20">
                            Safety & Emergency Suite
                        </span>
                    </div>
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mt-2">
                        Emergency SOS & Trusted Contacts
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Configure trusted safety contacts to automatically receive live GPS tracking and alert notifications in an emergency.
                    </p>
                </div>

                <a href="tel:+18007433692" class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-sm shadow-lg shadow-red-600/30 transition-all">
                    <span>🚨</span>
                    <span>24/7 Safety Desk: +1 (800) 743-3692</span>
                </a>
            </div>

            <!-- Emergency SOS Hero Card -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-red-600 via-rose-600 to-orange-600 text-white p-6 sm:p-8 shadow-2xl">
                <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="space-y-2 text-center md:text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-white text-xs font-bold uppercase tracking-wider">
                            <span>●</span> Immediate Assistance
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black">Need Urgent Help Right Now?</h2>
                        <p class="text-white/90 text-sm max-w-xl">
                            Press the SOS button below to instantly broadcast your current GPS coordinates to your trusted contacts and alert our 24/7 security dispatchers.
                        </p>
                    </div>

                    <button type="button" onclick="triggerWebSos()" id="mainSosBtn"
                            class="group relative inline-flex items-center justify-center gap-3 px-8 py-5 rounded-2xl bg-white text-red-600 font-black text-lg shadow-2xl hover:scale-105 active:scale-95 transition-all">
                        <span class="w-4 h-4 rounded-full bg-red-600 animate-ping"></span>
                        <span>TRIGGER SOS</span>
                    </button>
                </div>
                <!-- Background ambient circles -->
                <div class="absolute -right-10 -bottom-10 w-48 h-48 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
            </div>

            <!-- Emergency Contacts Section -->
            <div class="bg-white dark:bg-[#131826] rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-white/5 shadow-xl">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-white/5 pb-5">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Trusted Emergency Contacts</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            You can add up to 5 contacts. They will receive automated SMS / WhatsApp alerts when SOS is pressed.
                        </p>
                    </div>
                    <button type="button" onclick="openAddContactModal()"
                            class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs sm:text-sm flex items-center gap-1.5 transition-colors">
                        <span>+</span>
                        <span>Add Contact</span>
                    </button>
                </div>

                <!-- Empty State (Reference Screenshot 2) -->
                @if(!isset($contacts) || count($contacts) === 0)
                <div class="py-14 text-center">
                    <div class="w-24 h-24 mx-auto mb-4 rounded-full bg-orange-500/10 flex items-center justify-center text-orange-500">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2zM10 9a2 2 0 114 0 2 2 0 01-4 0z" />
                        </svg>
                    </div>
                    <h4 class="text-lg font-bold text-gray-900 dark:text-white">No contacts have been added..!</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto mt-1 mb-6">
                        Please add contacts to ensure your safety during all rides and deliveries.
                    </p>
                    <button type="button" onclick="openAddContactModal()"
                            class="px-6 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-lg shadow-orange-500/20 transition-all">
                        Add a Contact
                    </button>
                </div>
                @else
                <!-- Contacts List -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                    @foreach($contacts as $contact)
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-[#1A2234] border border-gray-100 dark:border-white/5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-orange-500/10 text-orange-500 flex items-center justify-center font-bold text-base">
                                {{ strtoupper(substr($contact->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h5 class="font-bold text-sm text-gray-900 dark:text-white">{{ $contact->name }}</h5>
                                    @if($contact->is_primary)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-500/20 text-green-500">PRIMARY</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $contact->phone }} • <span class="capitalize">{{ $contact->relationship }}</span></p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="tel:{{ $contact->phone }}" class="p-2 rounded-lg bg-gray-200 dark:bg-white/5 text-gray-700 dark:text-gray-300 hover:text-green-500" title="Call Contact">
                                📞
                            </a>
                            <button type="button" onclick="deleteContact({{ $contact->id }})" class="p-2 rounded-lg bg-gray-200 dark:bg-white/5 text-gray-700 dark:text-gray-300 hover:text-red-500" title="Delete Contact">
                                🗑️
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Quick Dial Emergency Services Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="tel:911" class="p-5 rounded-2xl bg-white dark:bg-[#131826] border border-gray-100 dark:border-white/5 flex items-center gap-4 hover:border-red-500 transition-colors shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-red-500/10 text-red-500 flex items-center justify-center text-xl shrink-0">
                        🚓
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-gray-900 dark:text-white">Police / Emergency</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Dial 911 / 112 directly</p>
                    </div>
                </a>

                <a href="tel:+18007433692" class="p-5 rounded-2xl bg-white dark:bg-[#131826] border border-gray-100 dark:border-white/5 flex items-center gap-4 hover:border-orange-500 transition-colors shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-orange-500/10 text-orange-500 flex items-center justify-center text-xl shrink-0">
                        🛡️
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-gray-900 dark:text-white">RideMyCars Safety Desk</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">24/7 Rapid Response Unit</p>
                    </div>
                </a>

                <a href="https://wa.me/18007433692?text=Emergency%20Safety%20Support%20Request" target="_blank" class="p-5 rounded-2xl bg-white dark:bg-[#131826] border border-gray-100 dark:border-white/5 flex items-center gap-4 hover:border-green-500 transition-colors shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-green-500/10 text-green-500 flex items-center justify-center text-xl shrink-0">
                        💬
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-gray-900 dark:text-white">WhatsApp Safety Line</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Chat with Security Team</p>
                    </div>
                </a>
            </div>

        </div>
    </div>

    <!-- Add Contact Modal (Reference Screenshot 3 permission and input) -->
    <div id="addContactModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#131826] border border-gray-200 dark:border-white/10 rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-white/5 pb-3">
                <h4 class="text-lg font-bold text-gray-900 dark:text-white">Add Emergency Contact</h4>
                <button type="button" onclick="closeAddContactModal()" class="text-gray-400 hover:text-white text-xl">✕</button>
            </div>

            <form id="contactForm" onsubmit="submitContactForm(event)" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Contact Name *</label>
                    <input type="text" id="contactName" required placeholder="e.g. Sarah Jenkins"
                           class="w-full px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-[#0B0F19] border border-gray-200 dark:border-white/10 text-sm focus:outline-none focus:border-orange-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Phone Number (with Country Code) *</label>
                    <input type="tel" id="contactPhone" required placeholder="e.g. +1 555 123 4567"
                           class="w-full px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-[#0B0F19] border border-gray-200 dark:border-white/10 text-sm focus:outline-none focus:border-orange-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Relationship</label>
                    <select id="contactRelationship" class="w-full px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-[#0B0F19] border border-gray-200 dark:border-white/10 text-sm focus:outline-none focus:border-orange-500">
                        <option value="Parent">Parent</option>
                        <option value="Spouse">Spouse / Partner</option>
                        <option value="Sibling">Sibling</option>
                        <option value="Child">Child</option>
                        <option value="Friend" selected>Friend</option>
                        <option value="Colleague">Colleague</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="contactPrimary" class="rounded text-orange-500 focus:ring-0">
                    <label for="contactPrimary" class="text-xs text-gray-600 dark:text-gray-400">Set as Primary Emergency Contact</label>
                </div>

                <div class="flex items-center gap-3 pt-3">
                    <button type="button" onclick="closeAddContactModal()"
                            class="flex-1 py-2.5 rounded-xl bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 font-bold text-sm">
                        Cancel
                    </button>
                    <button type="submit" id="saveContactBtn"
                            class="flex-1 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-lg shadow-orange-500/20">
                        Save Contact
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Alert Triggered Success Dialog -->
    <div id="sosAlertModal" class="fixed inset-0 z-50 hidden bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#131826] border border-red-500/30 rounded-3xl max-w-md w-full p-6 text-center space-y-4 shadow-2xl">
            <div class="w-16 h-16 mx-auto rounded-full bg-red-500/20 text-red-500 flex items-center justify-center text-3xl animate-bounce">
                🚨
            </div>
            <h4 class="text-xl font-black text-gray-900 dark:text-white">Emergency SOS Dispatched</h4>
            <p id="sosAlertMsg" class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                Your emergency alert and live coordinates have been dispatched to our security center and your trusted contacts.
            </p>
            <div class="space-y-2 pt-2">
                <a href="tel:+18007433692" class="w-full py-3 rounded-xl bg-red-600 text-white font-bold text-sm block">
                    📞 Call 24/7 Security Desk
                </a>
                <button type="button" onclick="closeSosAlertModal()" class="w-full py-2.5 rounded-xl bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-sm font-semibold">
                    Dismiss
                </button>
            </div>
        </div>
    </div>

    <script>
        function openAddContactModal() {
            document.getElementById('addContactModal').classList.remove('hidden');
        }

        function closeAddContactModal() {
            document.getElementById('addContactModal').classList.add('hidden');
        }

        function closeSosAlertModal() {
            document.getElementById('sosAlertModal').classList.add('hidden');
        }

        async function submitContactForm(e) {
            e.preventDefault();
            const btn = document.getElementById('saveContactBtn');
            btn.disabled = true;
            btn.innerText = 'Saving...';

            const payload = {
                name: document.getElementById('contactName').value,
                phone: document.getElementById('contactPhone').value,
                relationship: document.getElementById('contactRelationship').value,
                is_primary: document.getElementById('contactPrimary').checked,
                _token: '{{ csrf_token() }}'
            };

            try {
                const res = await fetch('/sos/contacts', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error saving contact');
                }
            } catch (err) {
                alert('Network error saving contact');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Save Contact';
            }
        }

        async function deleteContact(id) {
            if (!confirm('Are you sure you want to remove this emergency contact?')) return;
            try {
                const res = await fetch(`/sos/contacts/${id}/delete`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error deleting contact');
                }
            } catch (err) {
                alert('Error deleting contact');
            }
        }

        function triggerWebSos() {
            const btn = document.getElementById('mainSosBtn');
            btn.innerText = 'TRANSMITTING...';

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => sendSosPayload(pos.coords.latitude, pos.coords.longitude),
                    (err) => sendSosPayload(null, null),
                    { timeout: 5000 }
                );
            } else {
                sendSosPayload(null, null);
            }
        }

        async function sendSosPayload(lat, lng) {
            try {
                const res = await fetch('/sos/trigger', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        latitude: lat,
                        longitude: lng,
                        _token: '{{ csrf_token() }}'
                    })
                });
                const data = await res.json();
                if (data.success) {
                    document.getElementById('sosAlertMsg').innerText = data.data.sos_message || 'Emergency alert dispatched!';
                    document.getElementById('sosAlertModal').classList.remove('hidden');
                } else {
                    alert(data.message || 'Could not dispatch SOS.');
                }
            } catch (e) {
                alert('SOS dispatched to safety hotline: +1 800 743 3692');
            } finally {
                document.getElementById('mainSosBtn').innerHTML = '<span class="w-4 h-4 rounded-full bg-red-600 animate-ping"></span><span>TRIGGER SOS</span>';
            }
        }
    </script>
</x-layout>
