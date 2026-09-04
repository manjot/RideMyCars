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
sed -i 's|^APP_URL=.*|APP_URL=https://ridemycars.com|' .env 2>/dev/null || true
sed -i 's/^MAIL_MAILER=.*/MAIL_MAILER=smtp/' .env 2>/dev/null || true
sed -i 's/^MAIL_HOST=.*/MAIL_HOST=mail.ridemycars.com/' .env 2>/dev/null || true
sed -i 's/^MAIL_PORT=.*/MAIL_PORT=465/' .env 2>/dev/null || true
sed -i 's/^MAIL_USERNAME=.*/MAIL_USERNAME=support@ridemycars.com/' .env 2>/dev/null || true
sed -i 's/^MAIL_PASSWORD=.*/MAIL_PASSWORD="Support@#007"/' .env 2>/dev/null || true
sed -i 's/^MAIL_ENCRYPTION=.*/MAIL_ENCRYPTION=ssl/' .env 2>/dev/null || true
sed -i 's/^MAIL_FROM_ADDRESS=.*/MAIL_FROM_ADDRESS="support@ridemycars.com"/' .env 2>/dev/null || true
sed -i 's/^ADMIN_INQUIRY_EMAIL=.*/ADMIN_INQUIRY_EMAIL=info@ridemycars.com/' .env 2>/dev/null || true
if ! grep -q "ADMIN_INQUIRY_EMAIL" .env; then
    echo "ADMIN_INQUIRY_EMAIL=info@ridemycars.com" >> .env
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
