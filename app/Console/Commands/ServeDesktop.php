<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use function Laravel\Prompts\note;
use function Laravel\Prompts\intro;
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
    }

    private function initTauriServer() : void
    {
        note( 'Starting Desktop App' );
        $tauriPath = base_path('src-tauri');
        note( 'Starting Desktop App at ' . $tauriPath );

        if (!File::exists($tauriPath . '/Cargo.toml')) {
            throw new \RuntimeException("Cargo.toml not found in src-tauri directory. Please add it to your project.");
        }

        note( 'File ' . File::exists( $tauriPath . '/target'  ) );

        if( !File::exists( $tauriPath . '/target'  ) )
        {
            // ->tty()
            Process::path( $tauriPath )->forever()->run( "cargo build" );
        }

        // ->tty()

        Process::forever()->run( "npm run dev:tauri:desktop -- --port=50003" );
    }

    private function initViteServer() : void
    {
        note( "Starting Vite Development Server" );

        Process::start( "npm run dev:vite:desktop" );
    }

    private function setupEnvironment(): void
    {
        note("Setting up environment for Tauri");
        $this->logDebug("Setting up environment variables");

        // Fix cargo permissions if needed
        if (!is_writable(getenv('HOME') . '/.cargo')) {
            $this->logToFile("Fixing cargo permissions");
            shell_exec('mkdir -p $HOME/.cargo && chmod -R 755 $HOME/.cargo');
        }

        // Rest of your code...
    }
}
