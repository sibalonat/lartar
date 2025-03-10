#!/bin/bash
# filepath: /home/mnplus/work/LARAVEL/lartar/docker/8.4/tauri-run.sh

cd /var/www/html

# Set environment variables
export DISPLAY=:99
export LIBGL_ALWAYS_SOFTWARE=1
export MESA_LOADER_DRIVER_OVERRIDE=swrast
export EGL_PLATFORM=surfaceless
export XDG_RUNTIME_DIR=/tmp/runtime-dir
export HOME=/home/sail
export RUST_BACKTRACE=1

echo "Starting Desktop App..."
# The --keep-alive is a custom flag we'll add to our command
php artisan serve:desktop --keep-alive > /var/www/html/storage/logs/tauri/tauri.log 2>&1

# Even if the command exits, keep the process running so supervisor doesn't restart it
echo "Tauri process completed. Keeping container alive."
exec tail -f /dev/null
