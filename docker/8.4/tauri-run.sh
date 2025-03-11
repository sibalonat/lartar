#!/bin/bash

# Use a fixed display for Xvfb
export DISPLAY=:99
echo "Setting DISPLAY=$DISPLAY"

# Configure GPU rendering settings
export LIBGL_ALWAYS_SOFTWARE=1
export MESA_LOADER_DRIVER_OVERRIDE=swrast
export EGL_PLATFORM=surfaceless
export LIBGL_DRI3_DISABLE=1

# Configure dconf alternatives
export GSETTINGS_BACKEND=memory
export DCONF_PROFILE=/dev/null
export XDG_RUNTIME_DIR=/tmp/runtime-dir

# Fix common GTK issues
export GDK_SYNCHRONIZE=1
export GDK_BACKEND=x11

# Tauri 2 specific environment variables
export CI=true
export TAURI_CLI_NO_DEV_SERVER_WAIT=true  # Use true, not 1
export TAURI_LINUX_AYATANA_APPINDICATOR=true

# Fix X11 permissions
mkdir -p /tmp/.X11-unix
chmod 1777 /tmp/.X11-unix

# Test if X server is accessible with retry
echo "Testing X server connection..."
for i in {1..5}; do
    if DISPLAY=:99 xdpyinfo > /dev/null 2>&1; then
        echo "X server connection successful"
        break
    fi
    echo "Attempt $i: X server not ready, waiting..."
    sleep 2
    if [ $i -eq 5 ]; then
        echo "ERROR: Cannot connect to X server after multiple attempts"
        echo "X server status: $(ps aux | grep Xvfb)"
    fi
done

# Debug info
echo "Starting Tauri 2 app with the following configuration:"
echo "User: $(whoami)"
echo "Display: $DISPLAY"
echo "Working directory: $(pwd)"
echo "XDG_RUNTIME_DIR: $XDG_RUNTIME_DIR"

cd /var/www/html

echo "Starting Desktop App..."

# Make sure logs directory exists
mkdir -p /var/www/html/storage/logs/tauri

# Test if X server is accessible
echo "Testing X server connection..."
if ! DISPLAY=:99 xdpyinfo > /dev/null 2>&1; then
    echo "ERROR: Cannot connect to X server :99"
    echo "X server status: $(ps aux | grep Xvfb)"
else
    echo "X server connection successful"
fi

# Run the application with proper permissions
if [ "$(whoami)" = "sail" ]; then
    # For Tauri 2, use cargo tauri directly
    export PATH="$HOME/.cargo/bin:$PATH"
    php artisan serve:desktop --debug > /var/www/html/storage/logs/tauri/tauri.log 2>&1
else
    # If running as root, switch to sail
    gosu sail bash -c 'export PATH="$HOME/.cargo/bin:$PATH" && php artisan serve:desktop --debug > /var/www/html/storage/logs/tauri/tauri.log 2>&1'
fi

# Keep the script running to prevent supervisor restart
echo "Tauri process completed. Keeping container alive."
exec tail -f /dev/null
