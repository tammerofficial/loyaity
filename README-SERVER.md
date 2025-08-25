# 🚀 Loyalty System Server Setup

## Quick Start

### Method 1: Using the Script (Recommended)
```bash
./start-server.sh
```

### Method 2: Manual Start
```bash
# Get your network IP
NETWORK_IP=$(ifconfig | grep "inet " | grep -v 127.0.0.1 | head -1 | awk '{print $2}')

# Start the server
php artisan serve --host=$NETWORK_IP --port=8000
```

## 🌐 Access URLs

- **Local Access**: http://localhost:8000
- **Network Access**: http://192.168.8.183:8000 (or your network IP)

## 📱 Features Available

- ✅ Customer Management
- ✅ Loyalty Cards
- ✅ Apple Wallet Pass Generation
- ✅ Points System
- ✅ Admin Dashboard

## 🔧 Configuration

The application is configured to use:
- **Database**: SQLite (local)
- **Mail**: Local SMTP
- **Apple Wallet**: Unsigned passes (for development)

## 🛑 Stopping the Server

Press `Ctrl+C` in the terminal where the server is running.

## 🔄 Restarting

If you need to restart the server:

1. Stop the current server: `Ctrl+C`
2. Run: `./start-server.sh`

## 📝 Notes

- The server automatically detects your network IP
- All routes are accessible from other devices on the same network
- Apple Wallet passes are generated without signing for development
- Customer creation includes automatic membership number generation
