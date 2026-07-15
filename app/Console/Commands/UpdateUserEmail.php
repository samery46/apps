<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class UpdateUserEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'app:update-user-email';

    protected $signature = 'user:update-email {user_id} {email}';
    protected $description = 'Update user email address';

    /**
     * The console command description.
     *
     * @var string
     */
    // protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $email = $this->argument('email');

        $user = User::find($userId);

        if ($user) {
            $user->email = $email;
            $user->save();
            $this->info('Email user berhasil diupdate.');
        } else {
            $this->error('User tidak ditemukan.');
        }
    }
}
