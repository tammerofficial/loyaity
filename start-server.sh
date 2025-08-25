#!/bin/bash

# Loyalty System Server Startup Script
# This script starts the Laravel development server on the network IP

echo "🚀 Starting Loyalty System Server..."

# Get the network IP
NETWORK_IP=$(ifconfig | grep "inet " | grep -v 127.0.0.1 | head -1 | awk '{print $2}')

if [ -z "$NETWORK_IP" ]; then
    echo "❌ Could not determine network IP"
    exit 1
fi

echo "📍 Network IP: $NETWORK_IP"
echo "🌐 Server will be available at: http://$NETWORK_IP:8000"
echo ""

# Stop any existing server
echo "🛑 Stopping any existing server..."
pkill -f "php.*serve" 2>/dev/null || true

# Wait a moment
sleep 2

# Start the server
echo "▶️  Starting server on $NETWORK_IP:8000..."
php artisan serve --host=$NETWORK_IP --port=8000

echo ""
echo "✅ Server started successfully!"
echo "🌐 Access your application at: http://$NETWORK_IP:8000"
echo "🛑 Press Ctrl+C to stop the server"
