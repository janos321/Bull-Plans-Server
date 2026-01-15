<?php

namespace App\Modules\Customer;

use Illuminate\Support\Facades\DB;

class CustomerService
{
    /**
     * GET – összes customer lekérése egy trénerhez
     * FE: Dictionary<string, CustomerData>
     */
    public function getCustomers(string $trainerEmail): array|object
    {
        $rows = DB::table('Customers')
            ->where('trainer_email', $trainerEmail)
            ->get();
    
        if ($rows->isEmpty()) {
            return (object)[];
        }
    
        $result = [];
    
        foreach ($rows as $row) {
            $decoded = json_decode($row->customer_data, true);
    
            if (!is_array($decoded)) {
                $decoded = [];
            }
    
            if (
                !isset($decoded['questionAndAnswer']) ||
                empty($decoded['questionAndAnswer'])
            ) {
                $decoded['questionAndAnswer'] = (object)[];
            }
    
            if (
                !isset($decoded['trainingDays']) ||
                empty($decoded['trainingDays']) ||
                array_is_list($decoded['trainingDays'])
            ) {
                $decoded['trainingDays'] = (object)[];
            }
    
            if (!isset($decoded['activeCustomer'])) {
                $decoded['activeCustomer'] = false;
            }
    
            $result[$row->customer_email] = $decoded;
        }
    
        return empty($result) ? (object)[] : $result;
    }


    /**
     * PUT – customer mentése / frissítése
     */
    public function saveCustomer(
        string $trainerEmail,
        string $customerEmail,
        array $customerData
    ): bool
    {
        $payload = json_encode(
            $customerData,
            JSON_UNESCAPED_UNICODE
        );

        $existing = DB::table('Customers')
            ->where('trainer_email', $trainerEmail)
            ->where('customer_email', $customerEmail)
            ->first();

        if ($existing) {
            return DB::table('Customers')
                ->where('id', $existing->id)
                ->update([
                    'customer_data' => $payload,
                    'update_time'   => now()
                ]) > 0;
        }

        return DB::table('Customers')->insert([
            'trainer_email'  => $trainerEmail,
            'customer_email' => $customerEmail,
            'customer_data'  => $payload,
            'update_time'    => now()
        ]);
    }
}
