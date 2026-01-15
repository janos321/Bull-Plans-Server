<?php
namespace App\Modules\LoginReg;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginRegService
{

    public function login(string $email, string $password): string
    {
        // 1) ADMIN
        $admin = DB::table('administrator')
            ->where('name', $email)
            ->first();
    
        if ($admin && Hash::check($password, $admin->password)) {
            return 'admin';
        }
    
        // 2) TRAINER
        $trainer = DB::table('Trainer')
            ->where('email', $email)
            ->first();
    
        if ($trainer && Hash::check($password, $trainer->password)) {
    
            if ((int)$trainer->login === 1) {
                return 'already_logged_in';
            }
    
            DB::table('Trainer')
                ->where('email', $email)
                ->update(['login' => 1]);
    
            return 'trainer';
        }
    
        // 3) USER
        $user = DB::table('Users')
            ->where('email', $email)
            ->first();
    
        if ($user && Hash::check($password, $user->password)) {
    
            if ((int)$user->login === 1) {
                return 'already_logged_in';
            }
    
            DB::table('Users')
                ->where('email', $email)
                ->update(['login' => 1]);
    
            return 'user';
        }
    
        return 'invalid';
    }


    public function registerUser(array $data): bool
    {
        $validData = $data['valid_data'] ?? [];
    
        if (!isset($validData['trainingDays']) || empty($validData['trainingDays'])) {
            $validData['trainingDays'] = (object)[];
        }
    
        if (!isset($validData['datas']) || empty($validData['datas'])) {
            $validData['datas'] = (object)[];
        }
    
        return DB::table('Users')->insert([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'date'         => $data['date'],
            'password'     => Hash::make($data['password']),
            'login'        => 1,
            'profile_data' => json_encode($data['profile_data'], JSON_UNESCAPED_UNICODE),
            'valid_data'   => json_encode($validData, JSON_UNESCAPED_UNICODE),
        ]);
    }
    
    public function registerTrainer(array $data): bool
    {
        return DB::table('Trainer')->insert([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'date'         => $data['date'],
            'password'     => Hash::make($data['password']),
            'login'        => 1,
            'profile_data' => json_encode($data['profile_data']),
            'valid_data'   => json_encode($data['valid_data'] ?? []),
        ]);
    }
    
    public function updatePassword(string $email, string $newPassword): bool
    {
        $newHash = Hash::make($newPassword);
    
        $affected =
            DB::table('Users')->where('email', $email)->update(['password' => $newHash]) +
            DB::table('Trainer')->where('email', $email)->update(['password' => $newHash]);
    
        return $affected > 0;
    }
        
    public function checkEmail(string $email): string
    {
        if (DB::table('Users')->where('email', $email)->exists()) {
            return 'user_exists';
        }
    
        if (DB::table('Trainer')->where('email', $email)->exists()) {
            return 'trainer_exists';
        }
    
        return 'available';
    }
}
