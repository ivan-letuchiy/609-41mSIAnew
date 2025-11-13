<?php

namespace App\Http\Controllers;

use App\Models\{Flat, House, Vote};
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OwnerController extends Controller
{
    public function dashboard(): View
    {
        $user = Auth::user();

        if (!$user || !$user->owner) {
            return redirect()->route('login')->withErrors('Владелец не найден.');
        }

        $owner = $user->owner;
        $flats = $owner->flats ? $owner->flats->load('house') : collect();

        return view('userPage', compact('owner', 'flats'));
    }

    public function removeOwnerFromFlat(Flat $flat): RedirectResponse
    {
        $owner = Auth::user()->owner;

        if ($owner) {
            $flat->owners()->detach($owner->id);
        }

        return redirect()->route('user.page')->with('success', 'Связь с квартирой успешно удалена.');
    }

    // Новый метод для удаления аккаунта пользователя
    public function deleteAccount(): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            // Удаляем связанные данные
            $owner = $user->owner;
            if ($owner) {
                $owner->flats()->detach();
                $owner->delete();
            }

            // Удаляем пользователя
            $user->delete();
        }

        return redirect()->route('main')->with('success', 'Ваш аккаунт успешно удалён.');
    }

    // Метод для добавления квартиры владельцу
    public function showAddFlatForm(): View
    {
        $houses = House::all();
        return view('addFlat', compact('houses'));
    }

    public function saveFlat(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'house_id' => 'required|exists:houses,id',
            'flat_id' => 'required|exists:flats,id',
            'area' => 'required|numeric|min:10|max:500',
        ]);

        $owner = Auth::user()->owner;

        if ($owner) {
            $flat = Flat::findOrFail($validated['flat_id']);
            $owner->flats()->attach($flat, ['ownership_percentage' => 100]);
        }

        return redirect()->route('user.page')->with('success', 'Квартира успешно добавлена.');
    }

    public function updateFlat(Request $request, Flat $flat): RedirectResponse
    {
        $owner = Auth::user()->owner;

        if (!$owner) {
            return redirect()->route('login')->withErrors('Пользователь не найден.');
        }

        $validated = $request->validate([
            'ownership_interest' => 'required|numeric|min:0.01|max:100',
        ]);

        $flat->owners()->updateExistingPivot($owner->id, [
            'ownership_percentage' => $validated['ownership_interest'],
        ]);

        return redirect()->route('user.flat', $flat->id)->with('success', 'Данные обновлены.');
    }

    public function showFlat(Flat $flat)
    {
        $flat->load('house.meetings.questions.answers', 'owners');
        $owner = Auth::user()->owner;
        $ownership = $flat->owners->where('id', $owner->id)->first();

        return view('flatInfo', [
            'flat' => $flat,
            'ownership' => $ownership,
        ]);
    }

    public function submitVote(Request $request)
    {
        $owner = Auth::user()->owner;

        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'exists:answers,id',
        ]);

        foreach ($validated['answers'] as $questionId => $answerId) {
            Vote::create([
                'owner_id' => $owner->id,
                'question_id' => $questionId,
                'vote_answer' => $answerId,
            ]);
        }

        return redirect()->back()->with('success', 'Ваш голос учтён.');
    }

    public function updateOwnership(Request $request, Flat $flat): RedirectResponse
    {
        $owner = Auth::user()->owner;

        $validated = $request->validate([
            'ownership_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $flat->owners()->updateExistingPivot($owner->id, [
            'ownership_percentage' => $validated['ownership_percentage'],
        ]);

        return redirect()->route('user.flat', $flat->id)->with('success', 'Доля в праве успешно обновлена.');
    }

}
