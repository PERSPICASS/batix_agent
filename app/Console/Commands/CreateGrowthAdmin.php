<?php

namespace App\Console\Commands;

use App\Models\AdminUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateGrowthAdmin extends Command
{
    protected $signature = 'growth:admin
                            {username : Identifiant de connexion}
                            {--name= : Nom affiché}
                            {--password= : Mot de passe (sinon demandé de façon masquée)}';

    protected $description = 'Crée ou met à jour un administrateur BATIX Growth';

    public function handle(): int
    {
        $username = (string) $this->argument('username');
        $password = (string) ($this->option('password') ?: $this->secret('Mot de passe'));

        if ($password === '') {
            $this->error('Un mot de passe est requis.');

            return self::FAILURE;
        }

        AdminUser::query()->updateOrCreate(
            ['username' => $username],
            [
                'name' => (string) ($this->option('name') ?: $username),
                'password' => Hash::make($password),
                'is_active' => true,
            ],
        );

        $this->info("Administrateur '{$username}' enregistré.");

        return self::SUCCESS;
    }
}
