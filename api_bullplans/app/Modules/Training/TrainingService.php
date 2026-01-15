<?php

namespace App\Modules\Training;

use Illuminate\Support\Facades\DB;

class TrainingService
{
    public function getTrainingData(string $email): array|object
    {
        $row = DB::table('TrainingData')
            ->where('email', $email)
            ->first();
    
        if (!$row || !$row->training_data) {
            return (object)[];
        }
    
        $decoded = json_decode($row->training_data, true);
    
        if (!is_array($decoded)) {
            return (object)[];
        }
    
        foreach ($decoded as $key => $value) {
            if (!is_array($value) || empty($value)) {
                $decoded[$key] = (object)[];
            }
        }
    
        return $decoded;
    }

    public function saveTrainingData(string $email, array $trainingData): bool
    {
        return DB::table('TrainingData')
            ->updateOrInsert(
                ['email' => $email],
                ['training_data' => json_encode($trainingData, JSON_UNESCAPED_UNICODE)]
            );
    }

}
