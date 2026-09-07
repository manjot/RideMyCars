<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* ==========================================================================
       RideMyCars Professional Colorful Admin Theme
       Typography, Vibrant Color Accents, Glassmorphism & Micro-animations
       ========================================================================== */

    :root {
        --font-sans: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        --brand-amber: #f59e0b;
        --brand-orange: #ea580c;
        --brand-emerald: #10b981;
        --brand-cyan: #06b6d4;
        --brand-indigo: #6366f1;
        --brand-violet: #8b5cf6;
        --brand-rose: #f43f5e;
    }

    body, html, .fi-body, .fi-sidebar, .fi-topbar, .fi-main {
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif !important;
        -webkit-font-smoothing: antialiased;
    }

    /* --------------------------------------------------------------------------
       1. Topbar Styling (Glassmorphism & Crisp Depth)
       -------------------------------------------------------------------------- */
    .fi-topbar {
        background: rgba(255, 255, 255, 0.88) !important;
        backdrop-filter: blur(14px) saturate(180%) !important;
        -webkit-backdrop-filter: blur(14px) saturate(180%) !important;
        border-bottom: 1px solid rgba(226, 232, 240, 0.9) !important;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.03) !important;
        transition: all 0.2s ease !important;
    }

    .dark .fi-topbar {
        background: rgba(15, 23, 42, 0.88) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.4) !important;
    }

    /* --------------------------------------------------------------------------
       2. Sidebar Styling (Vibrant, Premium & Organized)
       -------------------------------------------------------------------------- */
    .fi-sidebar {
        background: #ffffff !important;
        border-right: 1px solid rgba(226, 232, 240, 0.95) !important;
        box-shadow: 4px 0 24px -4px rgba(0, 0, 0, 0.03) !important;
    }

    .dark .fi-sidebar {
        background: #0b1120 !important;
        border-right: 1px solid rgba(255, 255, 255, 0.06) !important;
        box-shadow: 4px 0 24px -4px rgba(0, 0, 0, 0.5) !important;
    }

    /* Sidebar Brand Header Area */
    .fi-sidebar-header {
        padding: 1.25rem 1rem !important;
        background: linear-gradient(180deg, rgba(245, 158, 11, 0.04) 0%, transparent 100%) !important;
        border-bottom: 1px solid rgba(226, 232, 240, 0.8) !important;
    }

    .dark .fi-sidebar-header {
        background: linear-gradient(180deg, rgba(245, 158, 11, 0.08) 0%, transparent 100%) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
    }

    /* Sidebar Group Titles */
    .fi-sidebar-group-label {
        font-size: 0.6875rem !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.08em !important;
        color: #64748b !important;
        padding-top: 1rem !important;
        padding-bottom: 0.35rem !important;
    }

    .dark .fi-sidebar-group-label {
        color: #94a3b8 !important;
    }

    /* Sidebar Navigation Items */
    .fi-sidebar-item-button {
        border-radius: 0.75rem !important;
        font-weight: 600 !important;
        font-size: 0.85rem !important;
        padding: 0.55rem 0.75rem !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        margin-bottom: 0.15rem !important;
    }

    .fi-sidebar-item-button:hover {
        transform: translateX(3px) !important;
        background: rgba(245, 158, 11, 0.08) !important;
        color: #d97706 !important;
    }

    .dark .fi-sidebar-item-button:hover {
        background: rgba(245, 158, 11, 0.15) !important;
        color: #fbbf24 !important;
    }

    .fi-sidebar-item-label {
        font-weight: 600 !important;
        letter-spacing: -0.01em !important;
        white-space: normal !important;
        word-break: break-word !important;
    }

    .fi-sidebar-item-icon {
        width: 1.25rem !important;
        height: 1.25rem !important;
        color: #94a3b8 !important;
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), color 0.2s ease !important;
    }

    .fi-sidebar-item-button:hover .fi-sidebar-item-icon {
        transform: scale(1.14) !important;
        color: #f59e0b !important;
    }

    /* Active Item - Sunset Gradient Glow Pill */
    .fi-sidebar-item-active .fi-sidebar-item-button,
    .fi-sidebar-item-button[aria-current="page"] {
        background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 14px -1px rgba(234, 88, 12, 0.35), 0 2px 6px -1px rgba(245, 158, 11, 0.2) !important;
        transform: translateX(4px) !important;
    }

    .fi-sidebar-item-active .fi-sidebar-item-button * {
        color: #ffffff !important;
    }

    /* Navigation Count Badges */
    .fi-sidebar-item .fi-badge {
        background: rgba(245, 158, 11, 0.14) !important;
        color: #b45309 !important;
        border: 1px solid rgba(245, 158, 11, 0.3) !important;
        font-weight: 700 !important;
        font-size: 0.6875rem !important;
        border-radius: 9999px !important;
        padding: 0.15rem 0.55rem !important;
    }

    .dark .fi-sidebar-item .fi-badge {
        background: rgba(245, 158, 11, 0.2) !important;
        color: #fcd34d !important;
        border-color: rgba(245, 158, 11, 0.4) !important;
    }

    /* --------------------------------------------------------------------------
       3. Colorful Dashboard Stat Overview Cards
       -------------------------------------------------------------------------- */
    .fi-wi-stats-overview-stat {
        border-radius: 1.25rem !important;
        border: 1px solid rgba(226, 232, 240, 0.85) !important;
        background: #ffffff !important;
        box-shadow: 0 4px 20px -3px rgba(0, 0, 0, 0.04), 0 2px 6px -2px rgba(0, 0, 0, 0.02) !important;
        position: relative !important;
        overflow: hidden !important;
        transition: all 0.25s ease !important;
        padding: 1.35rem 1.5rem !important;
    }

    .dark .fi-wi-stats-overview-stat {
        background: #131d31 !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        box-shadow: 0 4px 24px -3px rgba(0, 0, 0, 0.4) !important;
    }

    .fi-wi-stats-overview-stat:hover {
        transform: translateY(-3px) !important;
        box-shadow: 0 12px 28px -4px rgba(0, 0, 0, 0.08), 0 4px 10px -2px rgba(0, 0, 0, 0.04) !important;
    }

    .dark .fi-wi-stats-overview-stat:hover {
        box-shadow: 0 12px 32px -4px rgba(0, 0, 0, 0.6) !important;
    }

    /* Colorful Top Accent Borders on Stat Cards */
    .fi-wi-stats-overview-stat:nth-child(1) {
        border-top: 4px solid #10b981 !important;
        background: linear-gradient(180deg, rgba(16, 185, 129, 0.04) 0%, #ffffff 50%) !important;
    }
    .dark .fi-wi-stats-overview-stat:nth-child(1) {
        background: linear-gradient(180deg, rgba(16, 185, 129, 0.08) 0%, #131d31 50%) !important;
    }

    .fi-wi-stats-overview-stat:nth-child(2) {
        border-top: 4px solid #f59e0b !important;
        background: linear-gradient(180deg, rgba(245, 158, 11, 0.04) 0%, #ffffff 50%) !important;
    }
    .dark .fi-wi-stats-overview-stat:nth-child(2) {
        background: linear-gradient(180deg, rgba(245, 158, 11, 0.08) 0%, #131d31 50%) !important;
    }

    .fi-wi-stats-overview-stat:nth-child(3) {
        border-top: 4px solid #06b6d4 !important;
        background: linear-gradient(180deg, rgba(6, 182, 212, 0.04) 0%, #ffffff 50%) !important;
    }
    .dark .fi-wi-stats-overview-stat:nth-child(3) {
        background: linear-gradient(180deg, rgba(6, 182, 212, 0.08) 0%, #131d31 50%) !important;
    }

    .fi-wi-stats-overview-stat:nth-child(4) {
        border-top: 4px solid #8b5cf6 !important;
        background: linear-gradient(180deg, rgba(139, 92, 246, 0.04) 0%, #ffffff 50%) !important;
    }
    .dark .fi-wi-stats-overview-stat:nth-child(4) {
        background: linear-gradient(180deg, rgba(139, 92, 246, 0.08) 0%, #131d31 50%) !important;
    }

    /* Stat Number Big Bold Display */
    .fi-wi-stats-overview-stat-value {
        font-size: 2rem !important;
        font-weight: 800 !important;
        letter-spacing: -0.03em !important;
        color: #0f172a !important;
        margin-top: 0.35rem !important;
        margin-bottom: 0.25rem !important;
    }

    .dark .fi-wi-stats-overview-stat-value {
        color: #f8fafc !important;
    }

    .fi-wi-stats-overview-stat-label {
        font-size: 0.8125rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        color: #64748b !important;
    }

    .dark .fi-wi-stats-overview-stat-label {
        color: #94a3b8 !important;
    }

    /* --------------------------------------------------------------------------
       4. Tables & Data Display Styling
       -------------------------------------------------------------------------- */
    .fi-ta {
        border-radius: 1.25rem !important;
        overflow: hidden !important;
        border: 1px solid rgba(226, 232, 240, 0.85) !important;
        box-shadow: 0 4px 20px -3px rgba(0, 0, 0, 0.04) !important;
        background: #ffffff !important;
    }

    .dark .fi-ta {
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        background: #111827 !important;
        box-shadow: 0 4px 24px -3px rgba(0, 0, 0, 0.4) !important;
    }

    .fi-ta-header {
        padding: 1.25rem 1.5rem !important;
        border-bottom: 1px solid rgba(226, 232, 240, 0.8) !important;
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.8) 0%, transparent 100%) !important;
    }

    .dark .fi-ta-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.03) 0%, transparent 100%) !important;
    }

    .fi-ta-header-heading {
        font-size: 1.15rem !important;
        font-weight: 800 !important;
        letter-spacing: -0.02em !important;
        color: #0f172a !important;
    }

    .dark .fi-ta-header-heading {
        color: #f8fafc !important;
    }

    /* Table Rows */
    .fi-ta-row {
        transition: background-color 0.15s ease !important;
    }

    .fi-ta-row:hover {
        background-color: rgba(245, 158, 11, 0.03) !important;
    }

    .dark .fi-ta-row:hover {
        background-color: rgba(255, 255, 255, 0.03) !important;
    }

    /* --------------------------------------------------------------------------
       5. Buttons, Actions & Badges
       -------------------------------------------------------------------------- */
    .fi-btn-primary {
        background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%) !important;
        border: none !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        border-radius: 0.65rem !important;
        box-shadow: 0 3px 10px -1px rgba(234, 88, 12, 0.35) !important;
        transition: all 0.2s ease !important;
    }

    .fi-btn-primary:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 16px -1px rgba(234, 88, 12, 0.45) !important;
    }

    /* Pills & Status Badges */
    .fi-badge {
        font-weight: 700 !important;
        border-radius: 9999px !important;
        padding: 0.2rem 0.65rem !important;
        letter-spacing: 0.02em !important;
    }

    /* Form Section Headers */
    .fi-section {
        border-radius: 1.25rem !important;
        border: 1px solid rgba(226, 232, 240, 0.85) !important;
        background: #ffffff !important;
        box-shadow: 0 4px 18px -3px rgba(0, 0, 0, 0.03) !important;
        margin-bottom: 1.5rem !important;
    }

    .dark .fi-section {
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        background: #111827 !important;
    }

    .fi-section-header-heading {
        font-weight: 800 !important;
        font-size: 1.05rem !important;
        letter-spacing: -0.01em !important;
    }

    /* --------------------------------------------------------------------------
       6. Form Inputs & Switches
       -------------------------------------------------------------------------- */
    .fi-input-wrp {
        border-radius: 0.75rem !important;
        transition: all 0.15s ease !important;
    }

    .fi-input-wrp:focus-within {
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2) !important;
        border-color: #f59e0b !important;
    }

    /* --------------------------------------------------------------------------
       7. Tabs Navigation Styling
       -------------------------------------------------------------------------- */
    .fi-tabs-item-button {
        font-weight: 700 !important;
        font-size: 0.875rem !important;
        border-radius: 0.75rem !important;
        padding: 0.6rem 1rem !important;
        transition: all 0.2s ease !important;
    }

    .fi-tabs-item-active .fi-tabs-item-button {
        background: rgba(245, 158, 11, 0.12) !important;
        color: #d97706 !important;
    }

    .dark .fi-tabs-item-active .fi-tabs-item-button {
        background: rgba(245, 158, 11, 0.2) !important;
        color: #fbbf24 !important;
    }

    /* --------------------------------------------------------------------------
       8. Responsive Polish
       -------------------------------------------------------------------------- */
    @media (max-width: 768px) {
        .fi-wi-stats-overview {
            grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
            gap: 1rem !important;
        }

        .fi-topbar-quick-chips {
            display: none !important;
        }
    }
</style>
