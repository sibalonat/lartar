#!/bin/bash

# Configure Tauri for headless operation
echo "Setting up headless Tauri environment..."

# Configure minimal environment for Tauri headless
export CI=true
export TAURI_CLI_NO_DEV_SERVER_WAIT=true
export TAURI_LINUX_AYATANA_APPINDICATOR=true

# Create required runtime directories
mkdir -p /var/www/html/storage/tmp/runtime-dir
export XDG_RUNTIME_DIR=/var/www/html/storage/tmp/runtime-dir
chmod 700 $XDG_RUNTIME_DIR

# Configure dconf alternatives
export GSETTINGS_BACKEND=memory
export DCONF_PROFILE=/dev/null

# Debug info
echo "Starting Tauri 2 app in headless mode with the following configuration:"
echo "User: $(whoami)"
echo "Working directory: $(pwd)"
echo "XDG_RUNTIME_DIR: $XDG_RUNTIME_DIR"

cd /var/www/html

echo "Starting Desktop App in headless mode..."

# Make sure logs directory exists
mkdir -p /var/www/html/storage/logs/tauri

# Run the application with proper permissions
if [ "$(whoami)" = "sail" ]; then
    # Set PATH for sail user
    export PATH="$HOME/.cargo/bin:$PATH"
    php artisan serve:desktop --headless --debug > /var/www/html/storage/logs/tauri/tauri.log 2>&1
else
    # If running as root, switch to sail
    gosu sail bash -c 'export PATH="$HOME/.cargo/bin:$PATH" && php artisan serve:desktop --headless --debug > /var/www/html/storage/logs/tauri/tauri.log 2>&1'
fi

# Keep the script running to prevent supervisor restart
echo "Tauri process completed. Keeping container alive."
exec tail -f /dev/null
