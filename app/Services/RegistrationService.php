<?php

namespace App\Services;


use App\Models\Flat;
use App\Models\Owner;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;


class RegistrationService
{
    public function registerUserWithFlat(array $data): void
    {
        try {
            $user = User::create([
                'name' => $data['full_name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
            ]);

            $owner = Owner::create([
                'full_name' => $data['full_name'],
                'ownership_interest' => 100,
                'user_id' => $user->id,
            ]);

            $flat = Flat::findOrFail($data['flat_id']);
            $flat->update(['area' => $data['area']]);

            $owner->flats()->attach($flat, ['ownership_percentage' => 100]);

            Log::info('User and owner registered successfully.', [
                'user' => $user,
                'owner' => $owner,
                'flat' => $flat,
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error('Flat not found for registration', ['message' => $e->getMessage()]);
            throw new Exception('Указанная квартира не найдена.');
        } catch (Exception $e) {
            Log::error('Error during user registration', ['message' => $e->getMessage()]);
            throw new Exception('Ошибка при регистрации пользователя.');
        }
    }
}
