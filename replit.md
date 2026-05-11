# TeamChat — Real-Time Team Chat App

A Slack/Discord-style real-time chat application built as a college final project.

## Tech Stack
- **Backend**: Laravel 13, PHP 8.4
- **Database**: MySQL 8 (socket: `/home/runner/.mysql/run/mysql.sock`)
- **Real-time**: Laravel Reverb (WebSocket broadcasting, Pusher protocol)
- **Frontend**: Blade templates, Tailwind CSS v4, Alpine.js
- **File uploads**: Cloudinary (unsigned upload widget)
- **Auth**: Laravel Breeze (Blade stack)

## Architecture
```
Browser → Node.js Proxy (port 5000)
            ├── HTTP requests     → Laravel (port 4000)
            └── WS /app/* upgrades → Reverb (port 8080)
```
The Node.js reverse proxy (`proxy.cjs`) runs on port 5000 (the only public Replit port) and routes traffic to the correct backend service.

## Running the App
```bash
bash start.sh
```
This script:
1. Starts MySQL (daemonized, socket-based)
2. Runs migrations + seeds if no users exist
3. Builds Vite assets (`npm run build`)
4. Starts Reverb on port 8080
5. Starts Laravel on port 4000
6. Starts Node.js proxy on port 5000

## Demo Accounts
| Email | Password |
|-------|----------|
| alice@example.com | password |
| bob@example.com   | password |

## Features
- [x] User registration and login
- [x] Create and join channels
- [x] Real-time messaging via WebSocket (Reverb)
- [x] Typing indicators (presence channel)
- [x] Online user presence sidebar
- [x] File/image attachments via Cloudinary
- [x] Dark mode UI throughout
- [x] Auto-scroll to latest messages
- [x] Auto-expanding textarea input

## Environment Variables (set as Replit secrets)
| Variable | Purpose |
|----------|---------|
| DB_CONNECTION | `mysql` |
| DB_SOCKET | `/home/runner/.mysql/run/mysql.sock` |
| BROADCAST_CONNECTION | `reverb` |
| SESSION_DRIVER | `file` |
| QUEUE_CONNECTION | `sync` |
| CACHE_STORE | `file` |
| REVERB_APP_KEY | Reverb app key (auto-generated) |
| REVERB_APP_SECRET | Reverb secret |
| REVERB_APP_ID | Reverb app ID |
| REVERB_HOST | `localhost` |
| REVERB_PORT | `8080` |
| REVERB_SCHEME | `http` |
| VITE_REVERB_APP_KEY | Same as REVERB_APP_KEY |
| CLOUDINARY_CLOUD_NAME | Your Cloudinary cloud name (optional) |
| CLOUDINARY_UPLOAD_PRESET | Your unsigned upload preset (optional) |

## User Preferences
- MySQL only (not SQLite)
- No over-engineering — clean, simple architecture
- Dark mode UI
