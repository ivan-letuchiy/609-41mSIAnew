<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\House;
use App\Models\Flat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class HousesControllerApi extends Controller
{
    public function index(Request $request)
    {
        return response(House::limit($request->perpage ?? 5)
            ->offset(($request->perpage ?? 5) * ($request->page ?? 0))
            ->get());
    }

    public function total()
    {
        return response(House::all()->count());
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        // 1. Проверка прав
        if (!Gate::allows('create-house')) {
            return response()->json(['code' => 1, 'message' => 'У вас нет прав']);
        }

        // 2. Валидация
        $validated = $request->validate([
            'house_name' => 'required|unique:houses|max:255',
            'image' => 'required|file|image|max:5120', // ограничение 5МБ
            'start_flat' => 'required|integer|min:1',
            'end_flat' => 'required|integer|gte:start_flat',
        ]);

        $file = $request->file('image');
        $fileName = time() . '_' . $file->getClientOriginalName();

        try {
            // 3. Загрузка в Yandex Cloud (S3)
            $path = Storage::disk('s3')->putFileAs(
                'house.pictures',
                $file,
                $fileName
            );

            // КРИТИЧЕСКОЕ ИСПРАВЛЕНИЕ: Проверяем, что файл действительно загружен
            if (!$path) {
                return response()->json([
                    'code' => 2,
                    'message' => 'Ошибка: Не удалось загрузить файл в Yandex Cloud. Проверьте ключи в .env'
                ], 500);
            }

            // Получение публичного URL (теперь $path точно не пустой)
            $url = Storage::disk('s3')->url($path);

            // 4. Транзакция БД
            DB::transaction(function () use ($validated, $url) {
                $house = new House();
                $house->house_name = $validated['house_name'];
                $house->picture_url = $url;
                $house->save();

                for ($i = $validated['start_flat']; $i <= $validated['end_flat']; $i++) {
                    $flat = new Flat();
                    $flat->house_id = $house->id;
                    $flat->apartment_number = $i;
                    $flat->area = 50.00;
                    $flat->save();
                }
            });

            return response()->json(['code' => 0, 'message' => 'Дом и квартиры созданы']);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 2,
                'message' => 'Ошибка: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id)
    {
        return response(House::find($id));
    }
}
