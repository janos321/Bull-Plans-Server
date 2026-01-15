<?php

namespace App\Modules\Offer;

use Illuminate\Support\Facades\DB;

class OfferService
{
    public function getOffers(): array|object
    {
        $rows = DB::table('Offers')->get();

        if ($rows->isEmpty()) {
            return (object)[];
        }
        
        $result = [];

        foreach ($rows as $row) {
            $decoded = json_decode($row->offers, true);
    
            if (empty($decoded)) {
                $decoded = (object)[];
            }
    
            $result[$row->email] = $decoded;
        }
    
        return empty($result) ? (object)[] : $result;
    }

    public function saveOffers(string $email, array $offers): bool
    {
        return DB::table('Offers')
            ->updateOrInsert(
                ['email' => $email],
                ['offers' => json_encode($offers)]
            );
    }
    
    public function getOffersByEmail(string $email): array
    {
        $row = DB::table('Offers')
            ->where('email', $email)
            ->first();

        if (!$row || !$row->offers) {
            return [];
        }

        $decoded = json_decode($row->offers, true);
        return is_array($decoded) ? $decoded : [];
    }
}
