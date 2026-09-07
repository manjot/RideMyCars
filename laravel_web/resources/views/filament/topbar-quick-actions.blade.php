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
</style>

<div class="rmc-topbar-belt">
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
