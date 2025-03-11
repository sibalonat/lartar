<?php
// filepath: /home/mnplus/work/LARAVEL/lartar/app/Console/Commands/ServeDesktop.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use function Laravel\Prompts\note;
use function Laravel\Prompts\intro;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class ServeDesktop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'serve:desktop {--headless : Run in headless mode} {--keep-alive : Keep the process running after launching Tauri} {--debug : Enable debug mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Launch Tauri 2 desktop application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        intro('Running Desktop Environment with Tauri 2');

        $this->setupEnvironment();
        $this->initViteServer();
        $this->initTauriServer();

        if ($this->option('keep-alive')) {
            $this->info('Keeping the process alive...');
            while (true) {
                sleep(60);
            }
        }

        return 0;
    }

    private function initTauriServer(): void
    {
        $headless = $this->option('headless');
        $mode = $headless ? 'Headless Mode' : 'Desktop App';
        note("Starting $mode");

        // Set necessary environment variables
        $env = "CI=true ";
        $env .= "TAURI_CLI_NO_DEV_SERVER_WAIT=true ";

        // Launch with or without headless flag
        $this->info("Launching Tauri application...");
        passthru("cd " . base_path() . " && $env npm run dev:tauri:desktop -- -- --port=50003", $result);

        $this->info("Tauri process exited with code: $result");
    }

    private function initViteServer(): void
    {
        note("Starting Vite Development Server");
        Process::start("npm run dev:vite:desktop");
    }

    private function setupEnvironment(): void
    {
        note("Setting up environment for Tauri 2");

        // Fix cargo permissions if needed
        $homeDir = getenv('HOME');
        $cargoDir = $homeDir . '/.cargo';
        $runtimeDir = "/tmp/runtime-dir";

        if (!is_dir($cargoDir)) {
            Log::info("Creating cargo directory in $cargoDir");
            shell_exec("mkdir -p $cargoDir && chmod -R 755 $cargoDir");
        }

        if (!is_writable($cargoDir)) {
            Log::info("Fixing cargo permissions for $cargoDir");
            shell_exec("chmod -R 755 $cargoDir");
        }

        // Ensure runtime directory exists with proper permissions
        shell_exec("mkdir -p $runtimeDir && chmod 700 $runtimeDir");

        // Create log directory for debugging
        $logDir = storage_path('logs/tauri');
        if (!File::exists($logDir)) {
            File::makeDirectory($logDir, 0777, true);
        }

        // Test X server
        $this->info("Testing X server connection...");
        $xServerOutput = shell_exec("DISPLAY=:99 xdpyinfo 2>&1");

        if (strpos($xServerOutput, 'unable to open display') !== false) {
            $this->warn("X server not available. Check if Xvfb is running properly.");
            $this->warn("Xvfb status: " . shell_exec("ps aux | grep Xvfb"));
        } else {
            $this->info("X server connection confirmed.");
        }

        // Debug information
        Log::info("HOME directory: " . getenv('HOME'));
        Log::info("XDG_RUNTIME_DIR: " . $runtimeDir);
        Log::info("DISPLAY: " . getenv('DISPLAY'));
    }
}
