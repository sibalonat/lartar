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

        $this->initViteServer();
        $this->initTauriServer();
    }

    private function initTauriServer() : void
    {
        note( 'Starting Desktop App' );

        if( !File::exists( base_path( 'src-tauri/target' ) ) )
        {
            Process::path( 'src-tauri' )->forever()->tty()->run( "cargo build" );
        }

        Process::forever()->tty()->run( "npm run dev:tauri:desktop -- --port=50003" );
    }

    private function initViteServer() : void
    {
        note( "Starting Vite Development Server" );

        Process::start( "npm run dev:vite:desktop" );
    }

    private function buildCargo() : void
    {
        note( "Starting Vite Development Server" );

        Process::start( "npm run dev:vite:desktop" );
    }
}
