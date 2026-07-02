<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;

class PromoteUserRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:promote-user-role {email} {role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set a user\'s role (customer, vendor, support_agent, moderator, administrator, super_administrator).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $role = UserRole::tryFrom($this->argument('role'));

        if (! $role) {
            $this->error('Invalid role. Valid roles: '.implode(', ', array_column(UserRole::cases(), 'value')));

            return self::FAILURE;
        }

        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No user found with that email.');

            return self::FAILURE;
        }

        $user->update(['role' => $role->value]);

        $this->info("{$user->email} is now {$role->value}.");

        return self::SUCCESS;
    }
}
