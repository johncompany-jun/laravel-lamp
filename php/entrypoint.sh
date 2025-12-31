#!/bin/bash

# Change to Laravel directory
cd /var/www/html

# Install npm dependencies if node_modules doesn't exist
if [ ! -d "node_modules" ]; then
    echo "Installing npm dependencies..."
    npm install
fi

# Start Vite dev server in background
echo "Starting Vite dev server..."
npm run dev &

# Start Apache in foreground
echo "Starting Apache..."
apache2-foreground
