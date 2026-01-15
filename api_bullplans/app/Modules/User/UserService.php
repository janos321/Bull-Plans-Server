<?php

namespace App\Modules\User;

use Illuminate\Support\Facades\DB;

class UserService
{
    public function getUser(string $email): array|object
    {
        $row = DB::table('Users')->where('email', $email)->first();
        if (!$row) {
            return (object)[];
        }
    
        $data = (array)$row;
    
        $profileData = json_decode($data['profile_data'], true);
        if (empty($profileData)) {
            $profileData = (object)[];
        }
    
        $validData = json_decode($data['valid_data'], true);
        if (!is_array($validData)) {
            $validData = [];
        }
    
        if (!isset($validData['trainingDays']) || empty($validData['trainingDays'])) {
            $validData['trainingDays'] = (object)[];
        }
    
        if (!isset($validData['datas']) || empty($validData['datas'])) {
            $validData['datas'] = (object)[];
        }
    
        $data['profile_data'] = $profileData;
        $data['valid_data']   = $validData;
    
        return $data;
    }

    public function getValidData(string $email): array|object|null
    {
        $row = DB::table('Users')
            ->where('email', $email)
            ->first();
    
        if (!$row || !$row->valid_data) {
            return (object)[];
        }
    
        $validData = json_decode($row->valid_data, true);
    
        if (!is_array($validData)) {
            $validData = [];
        }
    
        if (
            !isset($validData['trainingDays']) ||
            empty($validData['trainingDays']) ||
            array_is_list($validData['trainingDays'])
        ) {
            $validData['trainingDays'] = (object)[];
        }
    
        if (
            !isset($validData['datas']) ||
            empty($validData['datas']) ||
            array_is_list($validData['datas'])
        ) {
            $validData['datas'] = (object)[];
        }
    
        return $validData;
    }

    public function logout(string $email, array $profileData): bool
    {
        return DB::table('Users')
            ->where('email', $email)
            ->update([
                'profile_data' => json_encode($profileData, JSON_UNESCAPED_UNICODE),
                'login'        => 0,
                'updated_at'   => now(),
            ]) > 0;
    }

    public function updateValidData(string $email, array $validData): bool
    {
        if (!isset($validData['trainingDays']) || empty($validData['trainingDays'])) {
            $validData['trainingDays'] = (object)[];
        }
    
        if (!isset($validData['datas']) || empty($validData['datas'])) {
            $validData['datas'] = (object)[];
        }
    
        return DB::table('Users')
            ->where('email', $email)
            ->update([
                'valid_data' => json_encode($validData, JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]) > 0;
    }


    public function updateProfile(string $email, array $profileData): bool
    {
        return DB::table('Users')
            ->where('email', $email)
            ->update([
                'profile_data' => json_encode($profileData, JSON_UNESCAPED_UNICODE),
                'updated_at'   => now(),
            ]) > 0;
    }
}
