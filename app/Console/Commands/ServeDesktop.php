<?php

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
    protected $signature = 'serve:desktop {--keep-alive : Keep the process running after launching Tauri}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        intro( 'Running Desktop Environment' );

        $this->setupEnvironment();

        $this->initViteServer();
        $this->initTauriServer();

        // After starting Tauri, add this:
        if ($this->option('keep-alive')) {
            $this->info('Keeping the process alive...');
            while (true) {
                sleep(60);
            }
        }

        return 0;
    }

    // private function initTauriServer() : void
    // {
    //     note( 'Starting Desktop App' );
    //     $tauriPath = base_path('src-tauri');
    //     note( 'Starting Desktop App at ' . $tauriPath );

    //     if (!File::exists($tauriPath . '/Cargo.toml')) {
    //         throw new \RuntimeException("Cargo.toml not found in src-tauri directory. Please add it to your project.");
    //     }

    //     note( 'File ' . File::exists( $tauriPath . '/target'  ) );

    //     if( !File::exists( $tauriPath . '/target'  ) )
    //     {
    //         // ->tty()
    //         Process::path( $tauriPath )->forever()->run( "cargo build" );
    //     }

    //     // ->tty()

    //     Process::forever()->run( "npm run dev:tauri:desktop -- --port=50003" );
    // }
    private function initTauriServer(): void
    {
        note('Starting Desktop App');
        $tauriPath = base_path('src-tauri');
        note('Starting Desktop App at ' . $tauriPath);

        if (!File::exists($tauriPath . '/Cargo.toml')) {
            throw new \RuntimeException("Cargo.toml not found in src-tauri directory. Please add it to your project.");
        }

        note('File ' . File::exists($tauriPath . '/target'));

        if (!File::exists($tauriPath . '/target')) {
            // Build using system command - this is blocking
            $this->info("Building Tauri target...");
            system("cd $tauriPath && cargo build", $result);
            if ($result !== 0) {
                throw new \RuntimeException("Failed to build Tauri target, error code: $result");
            }
        }

        // Run the Tauri command using system - this will block until Tauri exits
        $this->info("Launching Tauri application...");
        passthru("cd " . base_path() . " && DISPLAY=:99 npm run dev:tauri:desktop -- --port=50003", $result);

        $this->info("Tauri process exited with code: $result");
    }

    private function initViteServer() : void
    {
        note( "Starting Vite Development Server" );

        Process::start( "npm run dev:vite:desktop" );
    }

    private function setupEnvironment(): void
    {
        note("Setting up environment for Tauri");

        // Fix cargo permissions if needed
        $homeDir = getenv('HOME');
        $cargoDir = $homeDir . '/.cargo';

        if (!is_dir($cargoDir)) {
            Log::info("Creating cargo directory in $cargoDir");
            shell_exec("mkdir -p $cargoDir && chmod -R 755 $cargoDir");
        }

        if (!is_writable($cargoDir)) {
            Log::info("Fixing cargo permissions for $cargoDir");
            shell_exec("chmod -R 755 $cargoDir");
        }

        // Create log directory for debugging
        $logDir = storage_path('logs/tauri');
        if (!File::exists($logDir)) {
            File::makeDirectory($logDir, 0777, true);
        }

        // Debug information
        Log::info("HOME directory: " . getenv('HOME'));
        Log::info("XDG_RUNTIME_DIR: " . getenv('XDG_RUNTIME_DIR'));
        Log::info("DISPLAY: " . getenv('DISPLAY'));
    }
}
