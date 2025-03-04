<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use function Laravel\Prompts\note;
use function Laravel\Prompts\intro;
use Illuminate\Support\Facades\Process;

class ServeWeb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:serve-web';

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
        intro( 'Running Web Environment' );

        $this->initViteServer();
        $this->initPHPServer();
    }

    private function initViteServer() : void
    {
        note( "Starting Vite Development Server" );

        Process::start( "npm run dev:vite:web" );
    }

    private function initPHPServer() : void
    {
        note( "Starting PHP Server" );

        Process::forever()->tty()->run( "php artisan serve --port=50000" );
    }
}
