<?php

namespace Database\Seeders;

use App\Models\Beat;
use App\Models\User;
use App\Services\WorkWeekService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'hollowgrim@local.test'],
            [
                'name' => 'HollowGrim',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        Beat::query()->updateOrCreate(
            ['user_id' => $user->id, 'title' => 'Pays'],
            [
                'artists' => 'Westside Gunn x Griselda',
                'bpm' => 68,
                'musical_key' => 'A minor',
                'mood' => 'dark grimy boom bap',
                'status' => 'uploaded',
                'notes' => 'Best performer so far (~208 views).',
            ],
        );

        app(WorkWeekService::class)->currentWeek($user);
    }
}
