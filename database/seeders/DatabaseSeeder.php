<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Safe to run in production: MerchSeeder is idempotent (matches on
     * slug/color/size, never duplicates). The dev test user is skipped
     * outside local/testing — a known email+password pair has no business
     * existing in a real production database.
     *
     * Deliberately does not use User::factory() here — that pulls in Faker
     * via the fake() helper, which is unrelated to anything this app needs
     * seeded and has been unreliable in some local environments.
     */
    public function run(): void
    {
        if (app()->environment(['local', 'testing'])) {
            $user = User::firstOrCreate(
                ['email' => 'test@example.com'],
                [
                    'name' => 'Test User',
                    'password' => Hash::make('password'),
                ]
            );

            if (! $user->email_verified_at) {
                $user->forceFill([
                    'remember_token' => Str::random(10),
                    'email_verified_at' => now(),
                ])->save();
            }
        }

        $this->call(MerchSeeder::class);
    }
}
