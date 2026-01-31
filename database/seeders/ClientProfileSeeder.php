<?php

namespace Database\Seeders;

use App\Enums\User\UserRole;
use App\Models\ClientProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientProfileSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $users = [];

        for ($i = 1; $i <= 1000; $i++) {
            $users[] = [
                'email' => "client{$i}@test.uz",
                'password' => '$2y$12$lXIlfYRp1AQc33dqjJpB/.aYHQT8rGp0Qhz0WXx2OKWlYSE2dQMpO',
                'role' => UserRole::CLIENT,
                'remember_token' => Str::random(10),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('users')->insert($users);

        $userIds = DB::table('users')
            ->orderByDesc('id')
            ->limit(1000)
            ->pluck('id')
            ->reverse()
            ->values()
            ->toArray();

        $data = ClientProfile::factory()
            ->count(1000)
            ->make()
            ->map(function ($profile, $index) use ($userIds, $now) {
                return [
                    'user_id' => $userIds[$index],
                    'is_online' => $profile->is_online,
                    'is_active' => $profile->is_active,
                    'last_seen_at' => $profile->last_seen_at,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->toArray();

        DB::table('client_profiles')->insert($data);
    }
}