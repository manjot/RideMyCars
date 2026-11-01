<x-filament-panels::page>
    <!-- Interactive Settings Search & Filter Bar (Issue 2.3) -->
    <div x-data="{
            search: new URLSearchParams(window.location.search).get('q') || '',
            catalog: [
                { label: 'Stripe Gateway (Cards & Apple Pay)', tab: 'Payment Gateways', keywords: 'stripe card apple pay publishable secret webhook test live' },
                { label: 'Authorize.Net (Visa Solution)', tab: 'Payment Gateways', keywords: 'authorizenet authorize visa login transaction client signature key' },
                { label: 'ExpressPay Ghana (MoMo & Cards)', tab: 'Payment Gateways', keywords: 'expresspay momo mobile money mtn telecel airteltigo ghana ghs merchant api' },
                { label: 'Cash on Drop-off (Direct Pay)', tab: 'Payment Gateways', keywords: 'cash drop-off direct pay physical' },
                { label: 'Nalo Solutions Ghana SMS', tab: 'SMS Gateway', keywords: 'nalo sms ghana otp sender id auth key prefix' },
                { label: 'Twilio Worldwide SMS', tab: 'SMS Gateway', keywords: 'twilio sms worldwide international sid token phone messaging' },
                { label: 'SMTP & Transactional Email', tab: 'Email & SMTP', keywords: 'email mail smtp host port encryption ssl tls username password from' },
                { label: 'Google & Apple Social Sign-In', tab: 'Social Logins', keywords: 'google apple oauth social login client id secret team key redirect' },
                { label: 'Google Maps & Distance Unit', tab: 'Maps & Geo', keywords: 'google maps api key geolocation distance unit km miles' },
                { label: 'Firebase & FCM Push Alerts', tab: 'Firebase & Push', keywords: 'firebase fcm push notification server key project sender storage' },
                { label: 'Quick Demo Login Accounts', tab: 'Demo Accounts', keywords: 'demo account rider driver customer sarah michael sipho password' },
                { label: 'Proximity Chauffeur Backup', tab: 'Proximity Backup', keywords: 'proximity backup chauffeur radius timeout attempts' },
                { label: 'Dispatch & Request Waiting Times', tab: 'Dispatch & Waiting Times', keywords: 'dispatch waiting time minutes ride delivery auto cancel unaccepted' }
            ],
            get matches() {
                const q = this.search.trim().toLowerCase();
                if (!q) return this.catalog;
                return this.catalog.filter(c =>
                    c.label.toLowerCase().includes(q) ||
                    c.tab.toLowerCase().includes(q) ||
                    c.keywords.toLowerCase().includes(q)
                );
            },
            activateTab(tabName) {
                const buttons = Array.from(document.querySelectorAll('[role=tab], button.fi-tabs-item'));
                const target = buttons.find(b => b.textContent && b.textContent.trim().toLowerCase().includes(tabName.toLowerCase()));
                if (target) {
                    target.click();
                    target.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            },
            init() {
                if (this.search.trim().length > 0 && this.matches.length > 0) {
                    setTimeout(() => this.activateTab(this.matches[0].tab), 250);
                }
            }
        }"
        class="p-4 mb-4 bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-gray-900 dark:border-white/10">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span>🔍 Search Any Setting</span>
                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-amber-500/10 text-amber-500 border border-amber-500/20" x-text="matches.length + ' setting group(s)'"></span>
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Type a keyword (e.g., Stripe, MoMo, Nalo, Twilio, SMTP, Maps, Waiting Time, Backup) and click a result to jump directly to its tab.</p>
            </div>
            <div class="relative w-full md:w-96">
                <input type="search"
                       x-model="search"
                       @input="if (matches.length === 1) activateTab(matches[0].tab)"
                       placeholder="Search settings (e.g. MoMo, Stripe, SMS, SMTP, Waiting Time)..."
                       class="w-full px-3.5 py-2 text-sm bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white" />
            </div>
        </div>

        <div class="flex flex-wrap gap-2 mt-3" x-show="search.trim().length > 0 || true">
            <template x-for="item in matches" :key="item.label">
                <button type="button"
                        @click="activateTab(item.tab)"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border transition-all bg-gray-50 hover:bg-amber-500/10 border-gray-200 hover:border-amber-500/40 text-gray-700 dark:bg-gray-800/80 dark:border-white/10 dark:text-gray-200 dark:hover:text-amber-400">
                    <span x-text="item.label"></span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-500/15 text-amber-600 dark:text-amber-400" x-text="item.tab"></span>
                </button>
            </template>
            <div x-show="matches.length === 0" class="text-xs text-gray-400 py-1">
                No settings matched "<span x-text="search" class="font-semibold text-amber-400"></span>".
            </div>
        </div>
    </div>

    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}

        <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-white/10">
            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                <x-filament::icon icon="heroicon-o-shield-check" class="w-4 h-4 text-emerald-500" />
                <span>All changes are automatically cached and synchronized to runtime services upon saving.</span>
            </div>

            <x-filament::button type="submit" size="lg" icon="heroicon-o-check-circle" color="primary">
                Save All Settings
            </x-filament::button>
        </div>
    </x-filament-panels::form>
</x-filament-panels::page>
