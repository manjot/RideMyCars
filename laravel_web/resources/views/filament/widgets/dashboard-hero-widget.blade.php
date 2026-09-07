<x-filament-widgets::widget>
    <style>
        .rmc-hero-container {
            background: linear-gradient(135deg, #0b1120 0%, #1e1b4b 50%, #0f172a 100%) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            border-radius: 20px !important;
            padding: 24px 28px !important;
            color: #ffffff !important;
            box-shadow: 0 14px 38px -10px rgba(15, 23, 42, 0.45) !important;
            position: relative !important;
            overflow: hidden !important;
            margin-bottom: 8px !important;
        }

        .rmc-hero-glow-1 {
            position: absolute !important;
            right: -60px !important;
            top: -60px !important;
            width: 260px !important;
            height: 260px !important;
            background: rgba(245, 158, 11, 0.18) !important;
            border-radius: 9999px !important;
            filter: blur(50px) !important;
            pointer-events: none !important;
        }

        .rmc-hero-glow-2 {
            position: absolute !important;
            left: -60px !important;
            bottom: -60px !important;
            width: 260px !important;
            height: 260px !important;
            background: rgba(6, 182, 212, 0.18) !important;
            border-radius: 9999px !important;
            filter: blur(50px) !important;
            pointer-events: none !important;
        }

        .rmc-hero-header-row {
            display: flex !important;
            flex-wrap: wrap !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 16px !important;
            padding-bottom: 20px !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12) !important;
            position: relative !important;
            z-index: 2 !important;
        }

        .rmc-hero-title-wrap {
            flex: 1 1 320px !important;
        }

        .rmc-badge-row {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            margin-bottom: 6px !important;
        }

        .rmc-badge-pill {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            padding: 3px 10px !important;
            border-radius: 9999px !important;
            font-size: 10px !important;
            font-weight: 800 !important;
            letter-spacing: 0.05em !important;
            text-transform: uppercase !important;
        }

        .rmc-badge-amber {
            background: rgba(245, 158, 11, 0.2) !important;
            color: #fbbf24 !important;
            border: 1px solid rgba(245, 158, 11, 0.35) !important;
        }

        .rmc-badge-emerald {
            background: rgba(16, 185, 129, 0.2) !important;
            color: #34d399 !important;
            border: 1px solid rgba(16, 185, 129, 0.35) !important;
        }

        .rmc-pulse-dot {
            width: 7px !important;
            height: 7px !important;
            background: #34d399 !important;
            border-radius: 9999px !important;
            display: inline-block !important;
            box-shadow: 0 0 8px #34d399 !important;
        }

        .rmc-hero-title {
            color: #ffffff !important;
            font-size: 1.55rem !important;
            font-weight: 800 !important;
            line-height: 1.25 !important;
            margin: 0 !important;
            letter-spacing: -0.02em !important;
        }

        .rmc-hero-subtitle {
            color: #94a3b8 !important;
            font-size: 0.825rem !important;
            font-weight: 500 !important;
            margin: 4px 0 0 0 !important;
            line-height: 1.4 !important;
        }

        .rmc-hero-stat-chips {
            display: flex !important;
            flex-wrap: wrap !important;
            align-items: center !important;
            gap: 10px !important;
        }

        .rmc-stat-chip {
            background: rgba(255, 255, 255, 0.07) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            border-radius: 14px !important;
            padding: 8px 14px !important;
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            backdrop-filter: blur(8px) !important;
        }

        .rmc-chip-icon {
            font-size: 1.35rem !important;
            line-height: 1 !important;
        }

        .rmc-chip-label {
            font-size: 9px !important;
            text-transform: uppercase !important;
            font-weight: 700 !important;
            color: #94a3b8 !important;
            letter-spacing: 0.05em !important;
        }

        .rmc-chip-val-amber { color: #fbbf24 !important; font-size: 0.95rem !important; font-weight: 800 !important; line-height: 1.1 !important; }
        .rmc-chip-val-cyan { color: #38bdf8 !important; font-size: 0.95rem !important; font-weight: 800 !important; line-height: 1.1 !important; }
        .rmc-chip-val-emerald { color: #34d399 !important; font-size: 0.95rem !important; font-weight: 800 !important; line-height: 1.1 !important; }

        .rmc-actions-grid {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 14px !important;
            padding-top: 18px !important;
            position: relative !important;
            z-index: 2 !important;
        }

        @media (max-width: 1024px) {
            .rmc-actions-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }

        @media (max-width: 640px) {
            .rmc-actions-grid {
                grid-template-columns: repeat(1, 1fr) !important;
            }
        }

        .rmc-action-card {
            background: rgba(255, 255, 255, 0.06) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            border-radius: 16px !important;
            padding: 14px 16px !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            text-decoration: none !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        }

        .rmc-action-card:hover {
            background: rgba(255, 255, 255, 0.12) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 10px 24px -4px rgba(0, 0, 0, 0.35) !important;
        }

        .rmc-card-amber:hover { border-color: rgba(245, 158, 11, 0.6) !important; }
        .rmc-card-cyan:hover { border-color: rgba(6, 182, 212, 0.6) !important; }
        .rmc-card-emerald:hover { border-color: rgba(16, 185, 129, 0.6) !important; }
        .rmc-card-purple:hover { border-color: rgba(139, 92, 246, 0.6) !important; }

        .rmc-icon-box {
            width: 44px !important;
            height: 44px !important;
            border-radius: 12px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 1.35rem !important;
            flex-shrink: 0 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2) !important;
        }

        .rmc-ib-amber { background: linear-gradient(135deg, #f59e0b, #d97706) !important; }
        .rmc-ib-cyan { background: linear-gradient(135deg, #06b6d4, #0284c7) !important; }
        .rmc-ib-emerald { background: linear-gradient(135deg, #10b981, #059669) !important; }
        .rmc-ib-purple { background: linear-gradient(135deg, #8b5cf6, #6d28d9) !important; }

        .rmc-card-info {
            flex: 1 1 auto !important;
            min-width: 0 !important;
        }

        .rmc-card-title-row {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 4px !important;
        }

        .rmc-card-title {
            color: #ffffff !important;
            font-weight: 800 !important;
            font-size: 0.875rem !important;
            line-height: 1.2 !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        .rmc-card-arrow {
            color: #94a3b8 !important;
            font-size: 0.85rem !important;
            font-weight: 700 !important;
            transition: transform 0.2s ease, color 0.2s ease !important;
        }

        .rmc-action-card:hover .rmc-card-arrow {
            color: #ffffff !important;
            transform: translateX(3px) !important;
        }

        .rmc-card-sub {
            color: #cbd5e1 !important;
            font-size: 0.735rem !important;
            font-weight: 500 !important;
            margin-top: 2px !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }
    </style>

    <div class="rmc-hero-container">
        <div class="rmc-hero-glow-1"></div>
        <div class="rmc-hero-glow-2"></div>

        <!-- Top Row: Welcome & Metric Chips -->
        <div class="rmc-hero-header-row">
            <div class="rmc-hero-title-wrap">
                <div class="rmc-badge-row">
                    <span class="rmc-badge-pill rmc-badge-amber">Executive Dashboard</span>
                    <span class="rmc-badge-pill rmc-badge-emerald">
                        <span class="rmc-pulse-dot"></span>
                        Live System
                    </span>
                </div>
                <h1 class="rmc-hero-title">Welcome to RideMyCars Control Center ✨</h1>
                <p class="rmc-hero-subtitle">
                    Live platform dispatch, multi-currency pricing, and real-time operations across rides, rentals, and parcel delivery.
                </p>
            </div>

            <div class="rmc-hero-stat-chips">
                <div class="rmc-stat-chip">
                    <span class="rmc-chip-icon">🚕</span>
                    <div>
                        <div class="rmc-chip-label">Categories</div>
                        <div class="rmc-chip-val-amber">{{ $activeCategoriesCount }} Active</div>
                    </div>
                </div>

                <div class="rmc-stat-chip">
                    <span class="rmc-chip-icon">🚘</span>
                    <div>
                        <div class="rmc-chip-label">Fleet</div>
                        <div class="rmc-chip-val-cyan">{{ $totalVehiclesCount }} Cars</div>
                    </div>
                </div>

                <div class="rmc-stat-chip">
                    <span class="rmc-chip-icon">👨‍✈️</span>
                    <div>
                        <div class="rmc-chip-label">Chauffeurs</div>
                        <div class="rmc-chip-val-emerald">{{ $verifiedDriversCount }} Verified</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Row: 4 Responsive Action Cards (Explicit 4-Column Grid) -->
        <div class="rmc-actions-grid">
            <!-- 1. App Settings Hub -->
            <a href="{{ url('/admin/manage-app-settings') }}" class="rmc-action-card rmc-card-amber">
                <div class="rmc-icon-box rmc-ib-amber">⚙️</div>
                <div class="rmc-card-info">
                    <div class="rmc-card-title-row">
                        <span class="rmc-card-title">Settings Hub</span>
                        <span class="rmc-card-arrow">→</span>
                    </div>
                    <div class="rmc-card-sub">Stripe, Twilio, SMTP, OAuth</div>
                </div>
            </a>

            <!-- 2. Ride Categories & Pricing -->
            <a href="{{ url('/admin/ride-categories') }}" class="rmc-action-card rmc-card-cyan">
                <div class="rmc-icon-box rmc-ib-cyan">🚕</div>
                <div class="rmc-card-info">
                    <div class="rmc-card-title-row">
                        <span class="rmc-card-title">Ride Categories</span>
                        <span class="rmc-card-arrow">→</span>
                    </div>
                    <div class="rmc-card-sub">Base fares, rates & surge</div>
                </div>
            </a>

            <!-- 3. Delivery Radar -->
            <a href="{{ url('/admin/live-delivery-tracker-standalone') }}" class="rmc-action-card rmc-card-emerald">
                <div class="rmc-icon-box rmc-ib-emerald">⚡</div>
                <div class="rmc-card-info">
                    <div class="rmc-card-title-row">
                        <span class="rmc-card-title">Delivery Radar</span>
                        <span class="rmc-card-arrow">→</span>
                    </div>
                    <div class="rmc-card-sub">Live interactive GPS tracking</div>
                </div>
            </a>

            <!-- 4. Fleet & Rentals -->
            <a href="{{ url('/admin/vehicles') }}" class="rmc-action-card rmc-card-purple">
                <div class="rmc-icon-box rmc-ib-purple">🚗</div>
                <div class="rmc-card-info">
                    <div class="rmc-card-title-row">
                        <span class="rmc-card-title">Fleet & Rentals</span>
                        <span class="rmc-card-arrow">→</span>
                    </div>
                    <div class="rmc-card-sub">Vehicles & inspection checks</div>
                </div>
            </a>
        </div>
    </div>
</x-filament-widgets::widget>
