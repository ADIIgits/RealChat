#!/bin/bash
set -e

# Kill any processes still using our ports from a previous run
fuser -k 8080/tcp 2>/dev/null || true
fuser -k 5000/tcp 2>/dev/null || true
fuser -k 4000/tcp 2>/dev/null || true
sleep 1

# Initialize MySQL data dir if first run
if [ ! -d "/home/runner/.mysql/data/mysql" ]; then
  mkdir -p /home/runner/.mysql/data /home/runner/.mysql/run /home/runner/.mysql/logs
  mysqld --initialize-insecure --user=runner --datadir=/home/runner/.mysql/data 2>&1
fi

mkdir -p /home/runner/.mysql/run /home/runner/.mysql/logs

# Kill any stale socket/lock files from crashed instances
if [ -S /home/runner/.mysql/run/mysql.sock ] && ! mysqladmin --socket=/home/runner/.mysql/run/mysql.sock -u root ping --silent 2>/dev/null; then
  echo "Removing stale MySQL socket..."
  rm -f /home/runner/.mysql/run/mysql.sock /home/runner/.mysql/run/mysql.sock.lock /home/runner/.mysql/run/mysql.pid
fi

# Start MySQL if not already running
if ! mysqladmin --socket=/home/runner/.mysql/run/mysql.sock -u root ping --silent 2>/dev/null; then
  echo "Starting MySQL..."
  rm -f /home/runner/.mysql/run/mysql.pid
  mysqld --user=runner \
    --datadir=/home/runner/.mysql/data \
    --socket=/home/runner/.mysql/run/mysql.sock \
    --pid-file=/home/runner/.mysql/run/mysql.pid \
    --log-error=/home/runner/.mysql/logs/error.log \
    --mysqlx=OFF \
    --daemonize
  for i in $(seq 1 30); do
    if mysqladmin --socket=/home/runner/.mysql/run/mysql.sock -u root ping --silent 2>/dev/null; then
      echo "MySQL ready!"
      break
    fi
    sleep 1
  done
fi

# Create DB and user (idempotent)
mysql --socket=/home/runner/.mysql/run/mysql.sock -u root \
  -e "CREATE DATABASE IF NOT EXISTS laravel_chat;
      CREATE USER IF NOT EXISTS 'laravel'@'localhost' IDENTIFIED WITH mysql_native_password BY 'secret';
      GRANT ALL ON laravel_chat.* TO 'laravel'@'localhost';
      FLUSH PRIVILEGES;" 2>/dev/null || true

# Run migrations; if they fail due to stale tables, do a fresh run
php artisan migrate --force 2>&1 || php artisan migrate:fresh --force 2>&1

# Seed if no users exist
php artisan tinker --execute="if(App\Models\User::count()===0){Artisan::call('db:seed');echo 'Seeded';} else{echo 'Already seeded';}" 2>&1

php artisan config:clear 2>&1
php artisan view:clear 2>&1

# Start Reverb WebSocket server on port 8080 (internal)
php artisan reverb:start --host=0.0.0.0 --port=8080 &
echo "Reverb started (PID $!)"

# Build frontend assets (avoids CORS issues in Replit proxy environment)
echo "Building frontend assets..."
npm run build 2>&1

# Start Laravel on internal port 4000 (proxied via proxy.js)
php artisan serve --host=127.0.0.1 --port=4000 &
echo "Laravel started (PID $!)"

# Start Node.js reverse proxy on port 5000 (public Replit port)
# Routes /app/* WebSocket to Reverb, HTTP to Laravel
exec node proxy.cjs
