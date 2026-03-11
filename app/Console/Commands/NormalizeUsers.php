<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class NormalizeUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:normalize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normalize user emails to lowercase and reset admin/instructor/employee passwords to password123';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $targets = ['admin@maptech.com', 'instructor@maptech.com', 'employee@maptech.com'];

        User::all()->each(function (User $u) use ($targets) {
            $u->email = strtolower($u->email ?? '');

            if (in_array($u->email, $targets, true)) {
                $u->password = 'password123';
            }

            $u->save();
        });

        $this->info('Users normalized.');

        return Command::SUCCESS;
    }
}
