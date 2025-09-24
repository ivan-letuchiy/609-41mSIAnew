<?php

namespace App\Services;

use App\Models\{Flat, House};
use Exception;
use Illuminate\Support\Facades\Log;

class HouseService
{
    public function createHouseWithFlats(string $houseName, int $startApartment, int $endApartment): House
    {
        try {
            $house = House::create(['house_name' => $houseName]);

            for ($i = $startApartment; $i <= $endApartment; $i++) {
                Flat::create([
                    'apartment_number' => $i,
                    'house_id' => $house->id,
                    'area' => 50, // Задаём стандартное значение для площади
                ]);
            }

            return $house;
        } catch (Exception $e) {
            Log::error('Error creating house and flats', ['message' => $e->getMessage()]);
            throw new Exception('Ошибка при создании дома и квартир.');
        }
    }
}
