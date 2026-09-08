<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create {email} {name} {password} {--role=super_admin}';

    protected $description = 'Create (or update) an admin-side user and assign it a role';

    public function handle(): int
    {
        $user = User::updateOrCreate(
            ['email' => $this->argument('email')],
            [
                'name' => $this->argument('name'),
                'password' => $this->argument('password'),
                'is_active' => true,
                'locale' => 'el',
                'email_verified_at' => now(),
            ],
        );

        $user->syncRoles([$this->option('role')]);

        $this->info("User #{$user->id} ({$user->email}) is now '{$this->option('role')}'.");

        return self::SUCCESS;
    }
}
