<?php

namespace App\Console\Commands;

use App\Models\Status;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateSuperAdminCommand extends Command
{
    protected $signature = 'vrp:create-super-admin
                            {email : Adresse e-mail du super administrateur}
                            {name? : Nom affiché}
                            {--password= : Mot de passe (sinon demandé de façon sécurisée)}';

    protected $description = 'Crée un compte super administrateur plateforme (gestion des entreprises)';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        $name = trim((string) ($this->argument('name') ?: 'Super Admin'));
        $password = $this->option('password') ?: $this->secret('Mot de passe');

        $validator = Validator::make(
            compact('email', 'name', 'password'),
            [
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'name' => ['required', 'string', 'max:255'],
                'password' => ['required', Password::defaults()],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'company_id' => null,
            'status_id' => Status::superAdminId(),
            'mode' => 'Browse',
            'email_verified_at' => now(),
        ]);

        $this->info("Super administrateur créé : {$user->email} (id {$user->id})");

        return self::SUCCESS;
    }
}
