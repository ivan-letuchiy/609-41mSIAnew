<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Services\RegistrationService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserRegistrationController extends Controller
{
    private RegistrationService $registrationService;

    public function __construct(RegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    public function showRegistrationForm(): View
    {
        $houses = House::all(); // Получение списка всех домов
        return view('registrationPage', compact('houses')); // Передача $houses в представление
    }

    public function register(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'house_id' => 'required|exists:houses,id',
            'flat_id' => 'required|exists:flats,id',
            'area' => 'required|numeric|min:10|max:500',
        ]);

        try {
            $this->registrationService->registerUserWithFlat($validatedData);
            return redirect()->route('registration.success')->with('message', 'Регистрация прошла успешно!');
        } catch (\Exception $e) {
            return redirect()->route('registration.form')->withErrors('Ошибка регистрации: ' . $e->getMessage());
        }
    }

    public function getFlatsByHouse(House $house): JsonResponse
    {
        try {
            $flats = $house->flats()->select('id', 'apartment_number')->get();
            return response()->json($flats, 200);
        } catch (Exception $e) {
            Log::error('Ошибка получения квартир', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Ошибка получения данных'], 500);
        }
    }
}
