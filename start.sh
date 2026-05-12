#!/bin/bash
set -e

# Kill any processes still using our ports from a previous run
fuser -k 8080/tcp 2>/dev/null || true
fuser -k 5000/tcp 2>/dev/null || true
fuser -k 4000/tcp 2>/dev/null || true
sleep 1

# Patch .env with Neon PostgreSQL settings so Laravel web server uses correct DB
# (Laravel's built-in server reads .env directly; Replit shared env vars take effect for CLI but not always web context)
sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=pgsql|" .env
sed -i "s|^DB_HOST=.*|DB_HOST=ep-green-wind-aqsdh49q-pooler.c-8.us-east-1.aws.neon.tech|" .env
sed -i "s|^DB_PORT=.*|DB_PORT=5432|" .env
sed -i "s|^DB_DATABASE=.*|DB_DATABASE=neondb|" .env
sed -i "s|^DB_USERNAME=.*|DB_USERNAME=neondb_owner|" .env
sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=npg_8CGlodg1TakR|" .env
sed -i "s|^DB_SSLMODE=.*|DB_SSLMODE=require|" .env
# Remove legacy MySQL socket line if present
sed -i '/^DB_SOCKET=/d' .env
echo "DB patched to Neon PostgreSQL"

# Run migrations against Neon PostgreSQL (no local DB setup needed)
php artisan migrate --force 2>&1

# Seed if no users exist
php artisan tinker --execute="if(App\Models\User::count()===0){Artisan::call('db:seed');echo 'Seeded';} else{echo 'Already seeded';}" 2>&1

php artisan config:clear 2>&1
php artisan view:clear 2>&1

# Start Reverb WebSocket server on port 8080 (internal)
php artisan reverb:start --host=0.0.0.0 --port=8080 &
echo "Reverb started (PID $!)"

# Build frontend assets
echo "Building frontend assets..."
npm run build 2>&1

# Start Laravel on internal port 4000 (proxied via proxy.js)
php artisan serve --host=127.0.0.1 --port=4000 &
echo "Laravel started (PID $!)"

# Start Node.js reverse proxy on port 5000 (public Replit port)
exec node proxy.cjs
