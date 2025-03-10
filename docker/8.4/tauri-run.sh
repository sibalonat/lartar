#!/bin/bash
# Use WSL-specific display configuration
export DISPLAY=$(cat /etc/resolv.conf | grep nameserver | awk '{print $2}'):0.0
echo "Setting DISPLAY=$DISPLAY for WSL"

# Configure GPU rendering settings
export LIBGL_ALWAYS_SOFTWARE=1
export MESA_LOADER_DRIVER_OVERRIDE=swrast
export EGL_PLATFORM=surfaceless

# Configure dconf alternatives
export GSETTINGS_BACKEND=memory
export DCONF_PROFILE=/dev/null

# Debug info
echo "Starting Tauri app with the following configuration:"
echo "User: $(whoami)"
echo "Display: $DISPLAY"
echo "Working directory: $(pwd)"

cd /var/www/html

echo "Starting Desktop App..."

# Make sure logs directory exists
mkdir -p /var/www/html/storage/logs/tauri

# Run the application with proper permissions - just once!
if [ "$(whoami)" = "sail" ]; then
    # If running as sail user - add cargo to PATH
    PATH="$HOME/.cargo/bin:$PATH" php artisan serve:desktop --keep-alive > /var/www/html/storage/logs/tauri/tauri.log 2>&1
else
    # If running as root, switch to sail
    gosu sail bash -c 'PATH="$HOME/.cargo/bin:$PATH" php artisan serve:desktop --keep-alive > /var/www/html/storage/logs/tauri/tauri.log 2>&1'
fi

# Even if the command exits, keep the process running so supervisor doesn't restart it
echo "Tauri process completed. Keeping container alive."
exec tail -f /dev/null
