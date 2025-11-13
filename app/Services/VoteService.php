<?php

namespace App\Services;

use App\Models\{House, Meeting};
use Exception;
use Illuminate\Support\Facades\Log;

class VoteService
{
    public function getAllHouses(): \Illuminate\Database\Eloquent\Collection
    {
        try {
            return House::all();
        } catch (Exception $e) {
            Log::error('Error fetching houses', ['message' => $e->getMessage()]);
            throw new Exception('Ошибка при получении списка домов.');
        }
    }

    public function getMeetingsForHouse(int $houseId): \Illuminate\Support\Collection
    {
        try {
            return Meeting::where('house_id', $houseId)
                ->select('id', 'date')
                ->get();
        } catch (Exception $e) {
            Log::error('Error fetching meetings for house', ['message' => $e->getMessage()]);
            throw new Exception('Ошибка при получении собраний для дома.');
        }
    }
}
