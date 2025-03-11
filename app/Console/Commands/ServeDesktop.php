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
    protected $signature = 'serve:desktop';

    // {--headless : Run in headless mode} {--keep-alive : Keep the process running after launching Tauri} {--debug : Enable debug mode}

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

        $this->checkTauriProject();

        $this->initViteServer();
        $this->initTauriServer();

        // if ($this->option('keep-alive')) {
        //     $this->info('Keeping the process alive...');
        //     while (true) {
        //         sleep(60);
        //     }
        // }

        return 0;
    }

    private function initTauriServer(): void
    {
        // $headless = $this->option('headless');
        // $mode = $headless ? 'Headless Mode' : 'Desktop App';
        // note("Starting $mode");

        // Set necessary environment variables
        $env = "CI=true ";
        $env .= "TAURI_CLI_NO_DEV_SERVER_WAIT=true ";

        // Launch with or without headless flag
        $this->info("Launching Tauri application...");
        // passthru("cd " . base_path() . " && $env npm run dev:tauri:desktop -- --port=50003", $result);
        // Process::forever()->tty()->run( "npm run dev:tauri:desktop -- --port=50003" );

        Log::info("Launching Tauri application...");

        if( ! File::exists( base_path( 'src-tauri/target' ) ) )
        {
            Process::path( 'src-tauri' )->forever()->tty()->run( "cargo build" );
        }

        Log::info("Launching Tauri application...");

        Process::forever()->tty()->run( "npm run dev:tauri:desktop -- --port=50003" );


        // $this->info("Tauri process exited with code: $result");
        $this->info("Tauri process exited ");
    }

    private function initViteServer(): void
    {
        note("Starting Vite Development Server");
        Process::start("npm run dev:vite:desktop");
    }

    private function checkTauriProject(): void
    {
        $srcTauriDir = base_path('src-tauri');
        $cargoToml = $srcTauriDir . '/Cargo.toml';

        if (!File::exists($srcTauriDir)) {
            $this->error("src-tauri directory not found! Initializing Tauri project...");

            // Run tauri init
            passthru("cd " . base_path() . " && npx tauri init", $result);

            if ($result !== 0) {
                $this->error("Failed to initialize Tauri project. Please run 'npx tauri init' manually.");
                exit(1);
            }
        } elseif (!File::exists($cargoToml)) {
            $this->error("Cargo.toml not found in src-tauri directory.");
            $this->info("Attempting to repair Tauri project structure...");

            // Run tauri init again to repair
            passthru("cd " . base_path() . " && npx tauri init", $result);
        }

        $this->info("Tauri project structure verified!");
    }
}
