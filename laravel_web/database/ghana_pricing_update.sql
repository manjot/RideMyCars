-- ==============================================================================
-- RideMyCars - Ghana Pricing Update Script
-- Official Cost Matrix Calibration for Ghana (GHA / GHS / GH₵)
-- Can be executed directly in phpMyAdmin or MySQL CLI
-- ==============================================================================

-- 1. Ensure country_fares column exists on ride_categories table
SET @dbname = DATABASE();
SET @tablename = 'ride_categories';
SET @columnname = 'country_fares';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " JSON NULL AFTER multiplier;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 2. Upsert Ghana (GHA) record in country_pricings
INSERT INTO `country_pricings` (
    `country_code`,
    `country_name`,
    `currency_code`,
    `currency_symbol`,
    `exchange_rate`,
    `base_fare`,
    `per_km_rate`,
    `per_minute_rate`,
    `minimum_fare`,
    `cancellation_fee`,
    `additional_stop_fee`,
    `delivery_base_fare`,
    `delivery_per_km_rate`,
    `delivery_instant_addon`,
    `delivery_express_addon`,
    `delivery_same_day_addon`,
    `driver_hire_base_rate`,
    `driver_hire_hourly_rate`,
    `driver_hire_full_day_rate`,
    `is_active`,
    `is_default`,
    `created_at`,
    `updated_at`
) VALUES (
    'GHA',
    'Ghana',
    'GHS',
    'GH₵',
    1.0000,
    4.50,
    1.10,
    0.20,
    10.00,
    5.00,
    3.00,
    15.00,
    1.50,
    10.00,
    8.00,
    4.00,
    45.00,
    25.00,
    180.00,
    1,
    0,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    `country_name` = 'Ghana',
    `currency_code` = 'GHS',
    `currency_symbol` = 'GH₵',
    `exchange_rate` = 1.0000,
    `base_fare` = 4.50,
    `per_km_rate` = 1.10,
    `per_minute_rate` = 0.20,
    `minimum_fare` = 10.00,
    `cancellation_fee` = 5.00,
    `additional_stop_fee` = 3.00,
    `delivery_base_fare` = 15.00,
    `delivery_per_km_rate` = 1.50,
    `delivery_instant_addon` = 10.00,
    `delivery_express_addon` = 8.00,
    `delivery_same_day_addon` = 4.00,
    `driver_hire_base_rate` = 45.00,
    `driver_hire_hourly_rate` = 25.00,
    `driver_hire_full_day_rate` = 180.00,
    `is_active` = 1,
    `updated_at` = NOW();

-- 3. Upsert / Update the 6 Ride Categories with Ghana Country Fares
-- Tier 1: Economy (Kia Picanto, Hyundai i10)
INSERT INTO `ride_categories` (`name`, `slug`, `description`, `icon`, `capacity`, `multiplier`, `country_fares`, `is_active`, `sort_order`, `created_at`, `updated_at`)
VALUES (
    'Economy',
    'economy',
    'Affordable compact rides (Kia Picanto, Hyundai i10)',
    'heroicon-o-truck',
    4,
    1.00,
    '{"GHA": {"base_fare": 4.50, "per_km": 1.10, "min_fare": 8.50, "per_minute": 0.20, "currency": "GHS", "currency_symbol": "GH₵"}}',
    1,
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    `description` = 'Affordable compact rides (Kia Picanto, Hyundai i10)',
    `multiplier` = 1.00,
    `country_fares` = '{"GHA": {"base_fare": 4.50, "per_km": 1.10, "min_fare": 8.50, "per_minute": 0.20, "currency": "GHS", "currency_symbol": "GH₵"}}',
    `is_active` = 1,
    `sort_order` = 1,
    `updated_at` = NOW();

-- Tier 2: Standard / Comfort (Toyota Corolla)
INSERT INTO `ride_categories` (`name`, `slug`, `description`, `icon`, `capacity`, `multiplier`, `country_fares`, `is_active`, `sort_order`, `created_at`, `updated_at`)
VALUES (
    'Standard',
    'standard',
    'Comfortable sedans for daily commute (Toyota Corolla)',
    'heroicon-o-truck',
    4,
    1.35,
    '{"GHA": {"base_fare": 7.00, "per_km": 1.80, "min_fare": 23.50, "per_minute": 0.30, "currency": "GHS", "currency_symbol": "GH₵"}}',
    1,
    2,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    `description` = 'Comfortable sedans for daily commute (Toyota Corolla)',
    `country_fares` = '{"GHA": {"base_fare": 7.00, "per_km": 1.80, "min_fare": 23.50, "per_minute": 0.30, "currency": "GHS", "currency_symbol": "GH₵"}}',
    `is_active` = 1,
    `sort_order` = 2,
    `updated_at` = NOW();

-- Tier 3: Luxury SUV (Toyota Prado, Ford Explorer)
INSERT INTO `ride_categories` (`name`, `slug`, `description`, `icon`, `capacity`, `multiplier`, `country_fares`, `is_active`, `sort_order`, `created_at`, `updated_at`)
VALUES (
    'Luxury SUV',
    'suv',
    'Premium executive SUVs (Toyota Prado, Ford Explorer)',
    'heroicon-o-truck',
    6,
    1.85,
    '{"GHA": {"base_fare": 12.00, "per_km": 3.00, "min_fare": 35.20, "per_minute": 0.50, "currency": "GHS", "currency_symbol": "GH₵"}}',
    1,
    3,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    `name` = 'Luxury SUV',
    `description` = 'Premium executive SUVs (Toyota Prado, Ford Explorer)',
    `capacity` = 6,
    `country_fares` = '{"GHA": {"base_fare": 12.00, "per_km": 3.00, "min_fare": 35.20, "per_minute": 0.50, "currency": "GHS", "currency_symbol": "GH₵"}}',
    `is_active` = 1,
    `sort_order` = 3,
    `updated_at` = NOW();

-- Tier 4: Van XL (Hyundai H1)
INSERT INTO `ride_categories` (`name`, `slug`, `description`, `icon`, `capacity`, `multiplier`, `country_fares`, `is_active`, `sort_order`, `created_at`, `updated_at`)
VALUES (
    'Van XL',
    'van',
    'Spacious passenger vans for groups & extra luggage (Hyundai H1)',
    'heroicon-o-truck',
    8,
    2.20,
    '{"GHA": {"base_fare": 15.00, "per_km": 4.50, "min_fare": 50.20, "per_minute": 0.60, "currency": "GHS", "currency_symbol": "GH₵"}}',
    1,
    4,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    `name` = 'Van XL',
    `description` = 'Spacious passenger vans for groups & extra luggage (Hyundai H1)',
    `capacity` = 8,
    `country_fares` = '{"GHA": {"base_fare": 15.00, "per_km": 4.50, "min_fare": 50.20, "per_minute": 0.60, "currency": "GHS", "currency_symbol": "GH₵"}}',
    `is_active` = 1,
    `sort_order` = 4,
    `updated_at` = NOW();

-- Tier 5: VIP Chauffeurs (Mercedes E-Class, BMW 5 Series)
INSERT INTO `ride_categories` (`name`, `slug`, `description`, `icon`, `capacity`, `multiplier`, `country_fares`, `is_active`, `sort_order`, `created_at`, `updated_at`)
VALUES (
    'VIP Chauffeurs',
    'luxury',
    'High-end presidential travel with professional chauffeur (Mercedes E-Class, BMW 5 Series)',
    'heroicon-o-sparkles',
    4,
    3.00,
    '{"GHA": {"base_fare": 30.00, "per_km": 6.50, "min_fare": 109.50, "per_minute": 1.00, "currency": "GHS", "currency_symbol": "GH₵"}}',
    1,
    5,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    `name` = 'VIP Chauffeurs',
    `description` = 'High-end presidential travel with professional chauffeur (Mercedes E-Class, BMW 5 Series)',
    `country_fares` = '{"GHA": {"base_fare": 30.00, "per_km": 6.50, "min_fare": 109.50, "per_minute": 1.00, "currency": "GHS", "currency_symbol": "GH₵"}}',
    `is_active` = 1,
    `sort_order` = 5,
    `updated_at` = NOW();

-- Tier 6: Group Bus (7-14) (Toyota HiAce)
INSERT INTO `ride_categories` (`name`, `slug`, `description`, `icon`, `capacity`, `multiplier`, `country_fares`, `is_active`, `sort_order`, `created_at`, `updated_at`)
VALUES (
    'Group Bus (7–14)',
    'group-bus',
    'Minibus for delegations, events & airport group transfers (Toyota HiAce)',
    'heroicon-o-truck',
    14,
    4.00,
    '{"GHA": {"base_fare": 45.00, "per_km": 8.00, "min_fare": 150.90, "per_minute": 1.50, "currency": "GHS", "currency_symbol": "GH₵"}}',
    1,
    6,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    `name` = 'Group Bus (7–14)',
    `description` = 'Minibus for delegations, events & airport group transfers (Toyota HiAce)',
    `capacity` = 14,
    `country_fares` = '{"GHA": {"base_fare": 45.00, "per_km": 8.00, "min_fare": 150.90, "per_minute": 1.50, "currency": "GHS", "currency_symbol": "GH₵"}}',
    `is_active` = 1,
    `sort_order` = 6,
    `updated_at` = NOW();

-- Optional: Motorbike Category (Quick parcels & single passenger courier)
INSERT INTO `ride_categories` (`name`, `slug`, `description`, `icon`, `capacity`, `multiplier`, `country_fares`, `is_active`, `sort_order`, `created_at`, `updated_at`)
VALUES (
    'Motorbike',
    'bike',
    'Fast single passenger courier & rush transport',
    'heroicon-o-bolt',
    1,
    0.70,
    '{"GHA": {"base_fare": 3.00, "per_km": 0.80, "min_fare": 6.00, "per_minute": 0.15, "currency": "GHS", "currency_symbol": "GH₵"}}',
    1,
    7,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    `country_fares` = '{"GHA": {"base_fare": 3.00, "per_km": 0.80, "min_fare": 6.00, "per_minute": 0.15, "currency": "GHS", "currency_symbol": "GH₵"}}',
    `is_active` = 1,
    `sort_order` = 7,
    `updated_at` = NOW();
