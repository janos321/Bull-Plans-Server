<?php

namespace App\Modules\Motivation;

use Illuminate\Support\Facades\DB;

class MotivationService
{
    /**
     * GET – lekéri a mai vagy legutóbbi motivációt
     */
   public function getTodayMotivation(string $lang): ?string
    {
        $today = now()->toDateString();
    
        $row = DB::table('Motivations')
            ->whereDate('dateTime', $today)
            ->first();
    
        if (!$row) {
            $row = DB::table('Motivations')
                ->whereDate('dateTime', '<', $today)
                ->orderByDesc('dateTime')
                ->first();
        }
    
        if (!$row) {
            $row = DB::table('Motivations')
                ->orderBy('dateTime', 'asc')
                ->first();
        }
    
        if (!$row) {
            return null;
        }
    
        $translations = json_decode($row->motivation, true) ?? [];
    
        if (!is_array($translations) || empty($translations)) {
            return null;
        }
    
        return $translations[$lang] ?? reset($translations);
    }


    /**
     * POST – új motiváció beszúrása
     * automatikusan kezeli a dátumot
     */
    public function storeNewMotivation(array $translations): bool
    {
        $last = DB::table('Motivations')
            ->orderByDesc('dateTime')
            ->first();

        $today = now()->toDateString();
        $insertDate = $today;

        if ($last) {
            $lastDate = $last->dateTime;

            if ($lastDate == $today) {
                $insertDate = now()->addDay()->toDateString();
            }
            else if ($lastDate > $today) {
                $insertDate = date('Y-m-d', strtotime($lastDate . ' +1 day'));
            }
        }

        return DB::table('Motivations')->insert([
            'dateTime'   => $insertDate,
            'motivation' => json_encode($translations, JSON_UNESCAPED_UNICODE),
        ]);
    }
}
