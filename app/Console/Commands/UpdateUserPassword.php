<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateUserPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:update-password {user_id} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update user password';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $password = $this->argument('password');

        $user = User::find($userId);

        if ($user) {
            $user->password = Hash::make($password);
            $user->save();
            $this->info('Password user berhasil diupdate.');
        } else {
            $this->error('User tidak ditemukan.');
        }
    }
}
