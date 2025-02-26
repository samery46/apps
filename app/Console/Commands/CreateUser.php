<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user from the command line';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Tanyakan data user yang ingin dibuat
        $name = $this->ask('Masukkan nama user');
        $email = $this->ask('Masukkan email user');
        $password = $this->secret('Masukkan password');

        // Buat user baru
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        // Tampilkan informasi bahwa user berhasil dibuat
        $this->info("User baru berhasil dibuat dengan ID: {$user->id}");

        return 0;
    }
}
