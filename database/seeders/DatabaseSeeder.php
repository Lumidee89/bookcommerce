<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([RolesAndPermissionsSeeder::class, PlatformSeeder::class]);

        $email = env('OWNER_EMAIL', 'owner@yemiadebanjo.test');
        if (! User::where('email', $email)->exists()) {
            $password = env('OWNER_PASSWORD') ?: (app()->isLocal() ? 'password' : Str::password(16));
            $owner = User::create(['name' => 'Yemi Adebanjo', 'email' => $email, 'password' => $password, 'email_verified_at' => now()]);
            $owner->assignRole('owner');
            $this->command?->warn("Owner account: {$email} / {$password} — change this password after signing in.");
        }

        if (app()->isLocal()) {
            $this->call(SampleContentSeeder::class);
        }
    }
}
