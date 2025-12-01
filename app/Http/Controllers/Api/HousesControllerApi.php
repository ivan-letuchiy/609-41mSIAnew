<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\House;
use App\Models\Flat; // Импортируем модель Flat
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB; // Импортируем фасад DB для транзакций

class HousesControllerApi extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
    {
        return response(House::limit($request->perpage ?? 5)
            ->offset(($request->perpage ?? 5) * ($request->page ?? 0))
            ->get());
    }

    public function total(): \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
    {
        return response(House::all()->count());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        // 1. Проверка прав (как в методичке)
        if (!Gate::allows('create-house')) {
            return response()->json([
                'code' => 1,
                'message' => 'У вас нет прав на добавление дома'
            ]);
        }

        // 2. Валидация входных данных
        $validated = $request->validate([
            'house_name' => 'required|unique:houses|max:255',
            'image' => 'required|file',
            // Валидация диапазона квартир
            'start_flat' => 'required|integer|min:1',
            'end_flat' => 'required|integer|gte:start_flat', // gte: Greater Than or Equal (больше или равно)
        ]);

        $file = $request->file('image');
        // Генерация уникального имени файла
        $fileName = rand(1, 100000) . '_' . $file->getClientOriginalName();

        try {
            // 3. Загрузка файла в S3
            $path = Storage::disk('s3')->putFileAs(
                'house.pictures',
                $file,
                $fileName
            );

            // Получение публичного URL
            $url = Storage::disk('s3')->url($path);

            // 4. Транзакция БД (Создание Дома + Генерация Квартир)
            DB::transaction(function () use ($validated, $url) {

                // А. Создаем запись дома
                $house = new House();
                $house->house_name = $validated['house_name'];
                $house->picture_url = $url;
                $house->save();

                // Б. Генерируем квартиры в цикле
                for ($i = $validated['start_flat']; $i <= $validated['end_flat']; $i++) {
                    $flat = new Flat();
                    $flat->house_id = $house->id;       // Привязываем к ID только что созданного дома
                    $flat->apartment_number = $i;       // Номер квартиры
                    $flat->area = 50.00;                // Дефолтная площадь (можно изменить логику)
                    $flat->save();
                }
            });

            // 5. Успешный ответ
            return response()->json([
                'code' => 0,
                'message' => 'Дом и квартиры успешно созданы'
            ]);

        } catch (\Exception $e) {
            // Возврат кода ошибки 2 при сбое (S3 или БД)
            return response()->json([
                'code' => 2,
                'message' => 'Ошибка создания дома: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
    {
        return response(House::find($id));
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
