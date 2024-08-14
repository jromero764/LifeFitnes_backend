<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MensajeLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:mensaje';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Registra un mensaje en el log para verificar que la tarea se ejecutó correctamente';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info('La tarea programada se ejecutó correctamente a las ' . now());
        $this->info('Mensaje registrado en el log.');
    }
}
