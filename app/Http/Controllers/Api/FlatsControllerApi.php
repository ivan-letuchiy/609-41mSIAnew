<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; // Важно: подключаем базовый контроллер
use App\Models\Flat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FlatsControllerApi extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
    {
        return response(Flat::all());
    }

    /**
     * НОВЫЙ МЕТОД: Получение квартир текущего пользователя
     */
    public function myFlats(Request $request): \Illuminate\Http\JsonResponse
    {
        $userId = $request->user()->id;

        // 1. Получаем список квартир пользователя
        $flats = DB::table('flats')
            ->join('houses', 'flats.house_id', '=', 'houses.id')
            ->join('flat_owner', 'flats.id', '=', 'flat_owner.flat_id')
            ->join('owners', 'flat_owner.owner_id', '=', 'owners.id')
            ->where('owners.user_id', $userId)
            ->select(
                'flats.id',
                'flats.area',
                'flats.apartment_number as flat_number',
                'houses.house_name',
                'houses.picture_url',
                'flat_owner.ownership_percentage as share'
            )
            ->get();

        // 2. Для каждой квартиры находим всех собственников и их доли
        foreach ($flats as $flat) {
            $ownersData = DB::table('owners')
                ->join('flat_owner', 'owners.id', '=', 'flat_owner.owner_id')
                ->where('flat_owner.flat_id', $flat->id)
                ->select(
                    'owners.full_name as name',
                    'flat_owner.ownership_percentage as share'
                )
                ->get(); // Используем get(), чтобы получить массив объектов

            $flat->owners = $ownersData;
        }

        return response()->json($flats);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response(Flat::find($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
