<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MarkovReputationService;
use Illuminate\Console\Command;

class CreateReputationForUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reputation:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize reputation records for all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = new MarkovReputationService();
        $users = User::all();

        foreach ($users as $user) {
            $service->actualizarEstado($user);
            $this->info("✓ Reputación inicializada para: {$user->name}");
        }

        $this->info('✓ Todas las reputaciones creadas exitosamente');
    }
}
