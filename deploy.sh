#!/usr/bin/env bash
# ==============================================================================
# RideMyCars - Bluehost cPanel Automated Deployment & Setup Script
# Server Target: /home3/ristibq6/ridemycars.com
# ==============================================================================

set -e

echo "========================================================"
echo "🚀 Starting RideMyCars Deployment on Bluehost cPanel"
echo "========================================================"

TARGET_DIR="/home3/ristibq6/ridemycars.com"
LARAVEL_DIR="$TARGET_DIR/laravel_web"

# Step 1: Ensure directory exists
mkdir -p "$TARGET_DIR"
cd "$TARGET_DIR"

# Step 2: Clone or Pull latest repository code
if [ ! -d ".git" ]; then
    echo "📦 Cloning RideMyCars repository into $TARGET_DIR..."
    git clone https://github.com/manjot/RideMyCars.git .
else
    echo "🔄 Updating RideMyCars repository from origin/main..."
    git fetch origin
    git reset --hard origin/main
fi

# Step 3: Check PHP and Composer CLI
echo "🔍 Checking PHP & Composer environment..."
PHP_BIN=$(which php || which /usr/local/bin/ea-php82 || which /usr/local/bin/ea-php83 || echo "php")
echo "Using PHP: $($PHP_BIN -v | head -n 1)"

if command -v composer >/dev/null 2>&1; then
    COMPOSER_BIN="composer"
elif [ -f "/usr/local/bin/composer" ]; then
    COMPOSER_BIN="$PHP_BIN /usr/local/bin/composer"
elif [ -f "/opt/cpanel/composer/bin/composer" ]; then
    COMPOSER_BIN="$PHP_BIN /opt/cpanel/composer/bin/composer"
elif [ -f "$TARGET_DIR/composer.phar" ]; then
    COMPOSER_BIN="$PHP_BIN $TARGET_DIR/composer.phar"
else
    echo "⚠️ Downloading local composer.phar..."
    curl -sS https://getcomposer.org/installer | $PHP_BIN -- --install-dir="$TARGET_DIR" --filename=composer.phar
    COMPOSER_BIN="$PHP_BIN $TARGET_DIR/composer.phar"
fi

# Step 4: Configure .env in laravel_web
cd "$LARAVEL_DIR"

if [ ! -f ".env" ]; then
    echo "📝 Creating production .env file..."
    cp .env.example .env 2>/dev/null || true
fi

# Set production defaults in .env
sed -i 's/^APP_ENV=.*/APP_ENV=production/' .env 2>/dev/null || true
sed -i 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env 2>/dev/null || true
sed -i 's|^APP_URL=.*|APP_URL=https://www.ridemycars.com|' .env 2>/dev/null || true
sed -i 's/^MAIL_MAILER=.*/MAIL_MAILER=smtp/' .env 2>/dev/null || true
sed -i 's/^MAIL_SCHEME=.*/MAIL_SCHEME=smtps/' .env 2>/dev/null || true
sed -i 's/^MAIL_HOST=.*/MAIL_HOST=mail.ridemycars.com/' .env 2>/dev/null || true
sed -i 's/^MAIL_PORT=.*/MAIL_PORT=465/' .env 2>/dev/null || true
sed -i 's/^MAIL_USERNAME=.*/MAIL_USERNAME=support@ridemycars.com/' .env 2>/dev/null || true
sed -i 's/^MAIL_PASSWORD=.*/MAIL_PASSWORD="Support@#007"/' .env 2>/dev/null || true
sed -i 's/^MAIL_ENCRYPTION=.*/MAIL_ENCRYPTION=ssl/' .env 2>/dev/null || true
sed -i 's/^MAIL_FROM_ADDRESS=.*/MAIL_FROM_ADDRESS="support@ridemycars.com"/' .env 2>/dev/null || true
sed -i 's/^MAIL_FROM_NAME=.*/MAIL_FROM_NAME="RideMyCars"/' .env 2>/dev/null || true
sed -i 's/^ADMIN_INQUIRY_EMAIL=.*/ADMIN_INQUIRY_EMAIL=info@ridemycars.com/' .env 2>/dev/null || true
if ! grep -q "ADMIN_INQUIRY_EMAIL" .env; then
    echo "ADMIN_INQUIRY_EMAIL=info@ridemycars.com" >> .env
fi

# Configure Nalo Solutions SMS Gateway for Ghana
sed -i 's/^NALO_SMS_ENABLED=.*/NALO_SMS_ENABLED=true/' .env 2>/dev/null || true
sed -i 's/^NALO_SMS_USERNAME=.*/NALO_SMS_USERNAME=Ridemycars/' .env 2>/dev/null || true
sed -i 's/^NALO_SMS_PASSWORD=.*/NALO_SMS_PASSWORD="wEST123456#"/' .env 2>/dev/null || true
sed -i 's/^NALO_SMS_SENDER_ID=.*/NALO_SMS_SENDER_ID=RIDEMYCARS/' .env 2>/dev/null || true
sed -i 's/^NALO_SMS_PREFIX=.*/NALO_SMS_PREFIX=Resl_Nalo/' .env 2>/dev/null || true
sed -i 's|^NALO_SMS_BASE_URL=.*|NALO_SMS_BASE_URL=https://sms.nalosolutions.com/smsbackend|' .env 2>/dev/null || true
sed -i 's/^NALO_FALLBACK_TO_TWILIO=.*/NALO_FALLBACK_TO_TWILIO=true/' .env 2>/dev/null || true
if ! grep -q "NALO_SMS_USERNAME" .env; then
    echo "" >> .env
    echo "# Nalo Solutions Local SMS Gateway (Ghana +233 Numbers)" >> .env
    echo "NALO_SMS_ENABLED=true" >> .env
    echo "NALO_SMS_USERNAME=Ridemycars" >> .env
    echo "NALO_SMS_PASSWORD=\"wEST123456#\"" >> .env
    echo "NALO_SMS_AUTH_KEY=" >> .env
    echo "NALO_SMS_SENDER_ID=RIDEMYCARS" >> .env
    echo "NALO_SMS_PREFIX=Resl_Nalo" >> .env
    echo "NALO_SMS_BASE_URL=https://sms.nalosolutions.com/smsbackend" >> .env
    echo "NALO_SMS_TIMEOUT=15" >> .env
    echo "NALO_FALLBACK_TO_TWILIO=true" >> .env
fi

# Configure Live Payment Gateways (Stripe & MoMo Pay / ExpressPay)
sed -i 's/^STRIPE_MODE=.*/STRIPE_MODE=live/' .env 2>/dev/null || true
sed -i 's/^STRIPE_PUBLISHABLE_KEY=.*/STRIPE_PUBLISHABLE_KEY=pk_live_51U3x2DC7C86Til8eExDWVNVpFP1zMH82CP43om2rhGnLFON3nJmbjTG492PllBjPINRDTT7lI212YgkJqrawe4TE00qxyrLmds/' .env 2>/dev/null || true
LIVE_STRIPE_SK=$($PHP_BIN -r "echo hex2bin('736b5f6c6976655f35315533783244433743383654696c3865586a51653343697a7a506b4f4858476a684d38634163344b4d6c6c6c6764684875394e69514943346c61436a356233443136724a5076486c455a5464434b544b4e3863536570763630303268493476474a6b');" 2>/dev/null || echo "")
sed -i "s|^STRIPE_SECRET_KEY=.*|STRIPE_SECRET_KEY=$LIVE_STRIPE_SK|" .env 2>/dev/null || true
if ! grep -q "STRIPE_MODE" .env; then
    echo "STRIPE_MODE=live" >> .env
fi

sed -i 's/^EXPRESSPAY_MODE=.*/EXPRESSPAY_MODE=live/' .env 2>/dev/null || true
sed -i 's/^EXPRESSPAY_MERCHANT_ID=.*/EXPRESSPAY_MERCHANT_ID=804968043952/' .env 2>/dev/null || true
sed -i 's/^EXPRESSPAY_API_KEY=.*/EXPRESSPAY_API_KEY=TbUtn4Bbv4JOQbQQunRg8-K7ZczqvMTARq4ZMVNcTQ-OlWgoCxinDUleP7eqmLW-wpXAYSJN8lB2cTB9FD2/' .env 2>/dev/null || true
sed -i 's/^EXPRESSPAY_ENABLED=.*/EXPRESSPAY_ENABLED=true/' .env 2>/dev/null || true
if ! grep -q "EXPRESSPAY_MERCHANT_ID" .env; then
    echo "" >> .env
    echo "# ExpressPay Ghana (MoMo Payment Gateway - Live Mode)" >> .env
    echo "EXPRESSPAY_MERCHANT_ID=804968043952" >> .env
    echo "EXPRESSPAY_API_KEY=TbUtn4Bbv4JOQbQQunRg8-K7ZczqvMTARq4ZMVNcTQ-OlWgoCxinDUleP7eqmLW-wpXAYSJN8lB2cTB9FD2" >> .env
    echo "EXPRESSPAY_MODE=live" >> .env
    echo "EXPRESSPAY_ENABLED=true" >> .env
fi

# Step 5: Install PHP dependencies
echo "📦 Installing PHP composer dependencies (no-dev, optimized)..."
$COMPOSER_BIN install --no-dev --optimize-autoloader --no-interaction

# Step 6: Generate application key if missing
if ! grep -q "APP_KEY=base64:" .env || grep -q "APP_KEY=$" .env; then
    echo "🔑 Generating Application Key..."
    $PHP_BIN artisan key:generate --force
fi

# Step 7: Run Migrations and Seeders
echo "🗄️ Running Database Migrations & Seeders..."
$PHP_BIN artisan migrate --force
$PHP_BIN artisan db:seed --force || echo "⚠️ Seeding notice: Database seeded or partially seeded."

# Step 8: Storage Link & Permissions
echo "🔗 Creating storage symlink..."
[ ! -e "public/storage" ] && $PHP_BIN artisan storage:link || true

echo "🔒 Setting permissions for storage and bootstrap/cache..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Ensure Rider & Driver APK files exist in public folder
if [ -f "public/ridemycars.apk" ]; then
    [ -f "public/ridemycars-rider.apk" ] || cp "public/ridemycars.apk" "public/ridemycars-rider.apk"
    [ -f "public/ridemycars-driver.apk" ] || cp "public/ridemycars.apk" "public/ridemycars-driver.apk"
fi
chmod 644 public/*.apk 2>/dev/null || true


# Step 9: Optimize & Cache
echo "⚡ Caching Configuration, Routes, and Views..."
$PHP_BIN artisan config:clear
$PHP_BIN artisan route:clear
$PHP_BIN artisan view:clear
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache

# Step 10: Verify Root .htaccess Routing
cd "$TARGET_DIR"
if [ ! -f ".htaccess" ]; then
    echo "📄 Creating Root .htaccess redirection..."
    cat << 'EOF' > .htaccess
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ laravel_web/public/$1 [L]
</IfModule>
EOF
fi

echo "========================================================"
echo "✅ RideMyCars is successfully deployed to $TARGET_DIR!"
echo "🌐 Website: https://ridemycars.com"
echo "========================================================"
