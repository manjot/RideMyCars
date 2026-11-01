<style>
    .rmc-topbar-belt {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        margin-right: 14px !important;
    }

    .rmc-topbar-pill {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        padding: 5px 12px !important;
        border-radius: 9999px !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        text-decoration: none !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
        white-space: nowrap !important;
    }

    .rmc-topbar-pill:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1) !important;
    }

    .rmc-tb-live {
        background: #ecfdf5 !important;
        border: 1px solid #a7f3d0 !important;
        color: #065f46 !important;
    }
    .dark .rmc-tb-live {
        background: rgba(16, 185, 129, 0.15) !important;
        border-color: rgba(16, 185, 129, 0.3) !important;
        color: #34d399 !important;
    }

    .rmc-tb-radar {
        background: #f0fdf4 !important;
        border: 1px solid #bbf7d0 !important;
        color: #15803d !important;
    }
    .dark .rmc-tb-radar {
        background: rgba(16, 185, 129, 0.12) !important;
        border-color: rgba(16, 185, 129, 0.25) !important;
        color: #34d399 !important;
    }

    .rmc-tb-settings {
        background: #fffbeb !important;
        border: 1px solid #fde68a !important;
        color: #b45309 !important;
    }
    .dark .rmc-tb-settings {
        background: rgba(245, 158, 11, 0.12) !important;
        border-color: rgba(245, 158, 11, 0.25) !important;
        color: #fbbf24 !important;
    }

    .rmc-tb-categories {
        background: #f0f9ff !important;
        border: 1px solid #bae6fd !important;
        color: #0369a1 !important;
    }
    .dark .rmc-tb-categories {
        background: rgba(6, 182, 212, 0.12) !important;
        border-color: rgba(6, 182, 212, 0.25) !important;
        color: #38bdf8 !important;
    }

    .rmc-tb-site {
        background: #eef2ff !important;
        border: 1px solid #c7d2fe !important;
        color: #4338ca !important;
    }
    .dark .rmc-tb-site {
        background: rgba(99, 102, 241, 0.12) !important;
        border-color: rgba(99, 102, 241, 0.25) !important;
        color: #818cf8 !important;
    }

    @media (max-width: 860px) {
        .rmc-tb-label {
            display: none !important;
        }
        .rmc-topbar-pill {
            padding: 5px 8px !important;
        }
    }

    .rmc-admin-search-wrap {
        position: relative !important;
        display: flex !important;
        align-items: center !important;
        min-width: 240px !important;
        max-width: 340px !important;
    }
    .rmc-admin-search-input {
        width: 100% !important;
        padding: 6px 28px 6px 30px !important;
        border-radius: 9999px !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        background: #f8fafc !important;
        border: 1px solid #cbd5e1 !important;
        color: #0f172a !important;
        outline: none !important;
        transition: all 0.2s ease !important;
    }
    .dark .rmc-admin-search-input {
        background: rgba(15, 23, 42, 0.75) !important;
        border-color: rgba(255, 255, 255, 0.16) !important;
        color: #f8fafc !important;
    }
    .rmc-admin-search-input:focus {
        border-color: #f59e0b !important;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2) !important;
    }
    .rmc-admin-search-dropdown {
        position: absolute !important;
        top: calc(100% + 8px) !important;
        left: 0 !important;
        width: 380px !important;
        max-height: 420px !important;
        overflow-y: auto !important;
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 14px !important;
        box-shadow: 0 18px 40px rgba(0, 0, 0, 0.22) !important;
        z-index: 9999 !important;
        padding: 8px !important;
    }
    .dark .rmc-admin-search-dropdown {
        background: #0f172a !important;
        border-color: rgba(255, 255, 255, 0.14) !important;
        box-shadow: 0 18px 40px rgba(0, 0, 0, 0.55) !important;
    }
    .rmc-search-item {
        display: flex !important;
        align-items: flex-start !important;
        gap: 10px !important;
        padding: 8px 10px !important;
        border-radius: 10px !important;
        text-decoration: none !important;
        transition: background 0.15s ease !important;
    }
    .rmc-search-item:hover {
        background: rgba(245, 158, 11, 0.12) !important;
    }
    .rmc-search-item-title {
        font-size: 12.5px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        line-height: 1.25 !important;
    }
    .dark .rmc-search-item-title {
        color: #f8fafc !important;
    }
    .rmc-search-item-sub {
        font-size: 11px !important;
        color: #64748b !important;
        margin-top: 2px !important;
    }
    .dark .rmc-search-item-sub {
        color: #94a3b8 !important;
    }
</style>

<div class="rmc-topbar-belt">
    <!-- Admin Settings Search Bar (Issue 2.3) -->
    <div class="rmc-admin-search-wrap"
         x-data="{
            query: '',
            open: false,
            items: [
                { icon: '💳', title: 'Payment Gateways (Stripe, Authorize.Net, ExpressPay MoMo, Cash)', sub: 'App Settings Hub • Enable/disable gateways, Sandbox & Live API keys', keywords: 'payment stripe authorizenet expresspay momo mobile money apple pay cash card gateway keys', url: '{{ url('/admin/manage-app-settings?tab=-payment-gateways-tab') }}' },
                { icon: '💬', title: 'SMS Gateways (Nalo Solutions Ghana & Twilio Worldwide)', sub: 'App Settings Hub • OTP SMS sender ID, Nalo auth key, Twilio SID', keywords: 'sms otp nalo ghana twilio phone sender id messaging text alert', url: '{{ url('/admin/manage-app-settings?tab=-s-m-s-gateway-tab') }}' },
                { icon: '✉️', title: 'Email & SMTP Configuration', sub: 'App Settings Hub • SMTP host, port, SSL/TLS encryption, sender address', keywords: 'email mail smtp host port ssl tls password receipt otp notification', url: '{{ url('/admin/manage-app-settings?tab=-email-s-m-t-p-tab') }}' },
                { icon: '🔐', title: 'Social Logins (Google OAuth & Sign in with Apple)', sub: 'App Settings Hub • Client ID, Secret, Team ID, Redirect URI', keywords: 'social login oauth google apple sign in client id secret auth', url: '{{ url('/admin/manage-app-settings?tab=-social-logins-tab') }}' },
                { icon: '🗺️', title: 'Maps & Geolocation Settings', sub: 'App Settings Hub • Google Maps API Key, Distance unit (km / miles)', keywords: 'map google maps api key geolocation distance unit km miles autocomplete tracking', url: '{{ url('/admin/manage-app-settings?tab=-maps-geo-tab') }}' },
                { icon: '🔔', title: 'Firebase & Push Notifications', sub: 'App Settings Hub • FCM Server Key, Project ID, Messaging Sender ID', keywords: 'firebase push notification fcm server key project id cloud messaging alert', url: '{{ url('/admin/manage-app-settings?tab=-firebase-push-tab') }}' },
                { icon: '👤', title: 'Demo Accounts & Login Page Quick Fill', sub: 'App Settings Hub • Toggle demo box & configure demo rider/driver logins', keywords: 'demo account login customer driver password quick fill', url: '{{ url('/admin/manage-app-settings?tab=-demo-accounts-tab') }}' },
                { icon: '🛡️', title: 'Proximity Chauffeur Backup Settings', sub: 'App Settings Hub • Search radius, driver/customer timeout, max attempts', keywords: 'proximity backup chauffeur radius timeout attempts fallback', url: '{{ url('/admin/manage-app-settings?tab=-proximity-backup-tab') }}' },
                { icon: '⏱️', title: 'Dispatch & Request Waiting Time Settings', sub: 'App Settings Hub • Ride & Delivery waiting countdown, auto-cancel', keywords: 'dispatch waiting time minutes countdown auto cancel unaccepted request', url: '{{ url('/admin/manage-app-settings?tab=-dispatch-waiting-times-tab') }}' },
                { icon: '⚙️', title: 'System Settings & Mobile APK Downloads', sub: 'System Settings Resource • Raw key-value settings & APK file uploads', keywords: 'system settings apk android download key value raw config', url: '{{ url('/admin/settings') }}' },
                { icon: '🚕', title: 'Ride Categories & Fleet Pricing Rules', sub: 'Fleet & Fares • Base fare, per-km, per-min, minimum fare, waiting fee', keywords: 'ride categories pricing fare base per km minimum fleet vehicle', url: '{{ url('/admin/ride-categories') }}' },
                { icon: '🌍', title: 'Country Compliance Rules & Legal Policies', sub: 'Compliance • Country-specific TNC rules, taxes, and regulatory settings', keywords: 'country compliance rules legal california cpuc tax regulation', url: '{{ url('/admin/country-compliance-rules') }}' },
                { icon: '📊', title: 'Commissions & Revenue Analytics Report', sub: 'Financials • Commission rates, GHS & USD revenue, driver cash commission', keywords: 'commissions report revenue ghs usd earnings driver cash cut analytics', url: '{{ url('/admin/commissions-report') }}' },
                { icon: '🪪', title: 'Driver & Chauffeur Profiles & Verifications', sub: 'Drivers • Verify license, ID card, insurance, guarantor & online status', keywords: 'driver profile chauffeur verification license id card guarantor online badge', url: '{{ url('/admin/driver-profiles') }}' }
            ],
            get filtered() {
                const q = this.query.trim().toLowerCase();
                if (!q) return this.items;
                return this.items.filter(i =>
                    i.title.toLowerCase().includes(q) ||
                    i.sub.toLowerCase().includes(q) ||
                    i.keywords.toLowerCase().includes(q)
                );
            }
         }"
         @click.outside="open = false">
        <span style="position: absolute; left: 10px; font-size: 12px; pointer-events: none; opacity: 0.7;">🔍</span>
        <input type="text"
               class="rmc-admin-search-input"
               placeholder="Search all admin settings..."
               x-model="query"
               @focus="open = true"
               @keydown.escape="open = false"
               aria-label="Search all admin settings" />
        <button type="button"
                x-show="query.length > 0"
                @click="query = ''; open = false"
                style="position: absolute; right: 8px; font-size: 11px; color: #94a3b8; background: none; border: none; cursor: pointer;"
                title="Clear search">✕</button>

        <div class="rmc-admin-search-dropdown" x-show="open" x-transition style="display: none;">
            <div style="padding: 4px 8px 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: #f59e0b; display: flex; justify-content: space-between; align-items: center;">
                <span>Quick Settings & Platform Search</span>
                <span x-text="filtered.length + ' match(es)'"></span>
            </div>
            <template x-for="item in filtered" :key="item.title">
                <a :href="query.trim() ? (item.url + (item.url.includes('?') ? '&' : '?') + 'q=' + encodeURIComponent(query.trim())) : item.url"
                   class="rmc-search-item"
                   @click="open = false">
                    <span style="font-size: 16px; line-height: 1.2;" x-text="item.icon"></span>
                    <div style="flex: 1; min-width: 0;">
                        <div class="rmc-search-item-title" x-text="item.title"></div>
                        <div class="rmc-search-item-sub" x-text="item.sub"></div>
                    </div>
                </a>
            </template>
            <div x-show="filtered.length === 0" style="padding: 14px 10px; text-align: center; font-size: 12px; color: #94a3b8;">
                No matching setting found. Try searching in <a href="{{ url('/admin/manage-app-settings') }}" style="color: #f59e0b; text-decoration: underline;">App Settings Hub</a> or <a href="{{ url('/admin/settings') }}" style="color: #f59e0b; text-decoration: underline;">System Settings</a>.
            </div>
        </div>
    </div>

    <!-- Live Platform Operational Indicator -->
    <div class="rmc-topbar-pill rmc-tb-live" title="Global Dispatch Services Running">
        <span style="width: 7px; height: 7px; border-radius: 9999px; background: #10b981; display: inline-block; box-shadow: 0 0 6px #10b981;"></span>
        <span class="rmc-tb-label">Live System</span>
    </div>

    <!-- Quick Link: Live Delivery Radar -->
    <a href="{{ url('/admin/live-delivery-tracker-standalone') }}" 
       class="rmc-topbar-pill rmc-tb-radar"
       title="Live Interactive Dispatch & Delivery Radar">
        <span>⚡</span>
        <span class="rmc-tb-label">Radar</span>
    </a>

    <!-- Quick Link: App Settings Hub -->
    <a href="{{ url('/admin/manage-app-settings') }}" 
       class="rmc-topbar-pill rmc-tb-settings"
       title="Manage Payment Gateways, Twilio, SMTP & Social Logins">
        <span>⚙️</span>
        <span class="rmc-tb-label">Settings Hub</span>
    </a>

    <!-- Quick Link: Ride Categories & Pricing -->
    <a href="{{ url('/admin/ride-categories') }}" 
       class="rmc-topbar-pill rmc-tb-categories"
       title="Manage Vehicle Categories & Pricing Rules">
        <span>🚕</span>
        <span class="rmc-tb-label">Fleet & Fares</span>
    </a>

    <!-- External Link: Public Website -->
    <a href="{{ url('/') }}" 
       target="_blank" 
       rel="noopener noreferrer"
       class="rmc-topbar-pill rmc-tb-site"
       title="Open Public Customer Website in New Tab">
        <span>🌐</span>
        <span class="rmc-tb-label">Live Site</span>
        <span style="font-size: 10px; opacity: 0.7;">↗</span>
    </a>
</div>
