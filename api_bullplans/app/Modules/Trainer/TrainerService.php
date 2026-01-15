<?php

namespace App\Modules\Trainer;

use Illuminate\Support\Facades\DB;

class TrainerService
{
    public function get(string $email): ?array
    {
        $trainer = DB::table('Trainer')
            ->where('email', $email)
            ->first();

        if (!$trainer) {
            return null;
        }
        if (empty($validData)) {
            $validData = (object)[];
        }

        return [
            'name'         => $trainer->name,
            'email'        => $trainer->email,
            'date'         => $trainer->date,
            'profile_data' => json_decode($trainer->profile_data, true),
            'valid_data'   => $validData,
        ];
    }

    public function updateProfileData(string $email, array $profileData): bool
    {
        return DB::table('Trainer')
            ->where('email', $email)
            ->update([
                'profile_data' => json_encode($profileData, JSON_UNESCAPED_UNICODE),
                'updated_at'   => now(),
            ]) > 0;
    }

    public function logout(string $email, array $profileData): bool
    {
        return DB::table('Trainer')
            ->where('email', $email)
            ->update([
                'profile_data' => json_encode($profileData, JSON_UNESCAPED_UNICODE),
                'login'        => 0,
                'updated_at'   => now(),
            ]) > 0;
    }
}
