<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MeetingsControllerApi extends Controller
{
    public function index(Request $request): \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
    {
        return response(Meeting::limit($request->perpage ?? 5)
            ->offset(($request->perpage ?? 5) * ($request->page ?? 0))
            ->get());
    }

    public function total(): \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
    {
        return response(Meeting::all()->count());
    }

    /**
     * Возвращает собрания ТОЛЬКО для домов пользователя
     */
    public function indexWithStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $owner = Owner::where('user_id', $user->id)->first();

        // Если пользователь не является собственником вообще нигде - возвращаем пустой список
        if (!$owner) {
            return response()->json([]);
        }

        // 1. Получаем список ID домов, где у пользователя есть квартиры
        $houseIds = DB::table('flats')
            ->join('flat_owner', 'flats.id', '=', 'flat_owner.flat_id')
            ->where('flat_owner.owner_id', $owner->id)
            ->distinct()
            ->pluck('flats.house_id');

        // 2. Делаем выборку собраний только для этих домов
        $meetings = Meeting::select('meetings.*', 'houses.house_name')
            ->join('houses', 'meetings.house_id', '=', 'houses.id')
            ->whereIn('meetings.house_id', $houseIds)
            ->orderBy('meetings.date', 'desc')
            ->get();

        // 3. Проверяем, голосовал ли пользователь
        $meetings->transform(function ($meeting) use ($owner) {
            $hasVoted = DB::table('votes')
                ->join('questions', 'votes.question_id', '=', 'questions.id')
                ->where('questions.meeting_id', $meeting->id)
                ->where('votes.owner_id', $owner->id)
                ->exists();

            $meeting->has_voted = $hasVoted;
            return $meeting;
        });

        return response()->json($meetings);
    }

    public function show($id)
    {
        $meeting = Meeting::with('questions.answers')->findOrFail($id);
        return response()->json($meeting);
    }

    public function makeVote(Request $request)
    {
        $user = $request->user();
        $owner = Owner::where('user_id', $user->id)->first();

        if (!$owner) {
            return response()->json(['message' => 'Собственник не найден. Голосование невозможно.'], 403);
        }

        $data = $request->validate([
            'votes' => 'required|array',
            'votes.*.question_id' => 'required|exists:questions,id',
            'votes.*.answer' => 'required|string',
        ]);

        foreach ($data['votes'] as $voteItem) {
            $existingVote = DB::table('votes')
                ->where('owner_id', $owner->id)
                ->where('question_id', $voteItem['question_id'])
                ->first();

            if ($existingVote) {
                DB::table('votes')
                    ->where('id', $existingVote->id)
                    ->update([
                        'vote_answer' => $voteItem['answer'],
                        'updated_at' => now()
                    ]);
            } else {
                DB::table('votes')->insert([
                    'owner_id' => $owner->id,
                    'question_id' => $voteItem['question_id'],
                    'vote_answer' => $voteItem['answer'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return response()->json(['message' => 'Ваш голос успешно принят']);
    }

    public function results($id)
    {
        $meeting = Meeting::findOrFail($id);

        $questions = DB::table('questions')->where('meeting_id', $id)->get();

        $results = [];

        foreach ($questions as $question) {
            $stats = DB::table('votes')
                ->where('question_id', $question->id)
                ->select('vote_answer', DB::raw('count(*) as total'))
                ->groupBy('vote_answer')
                ->get();

            $totalVotes = $stats->sum('total');

            $results[] = [
                'question_text' => $question->question_text,
                'stats' => $stats,
                'total_votes' => $totalVotes
            ];
        }

        return response()->json([
            'meeting' => $meeting,
            'results' => $results
        ]);
    }

    /**
     * [АДМИН] Получить список собраний КОНКРЕТНОГО дома
     */
    public function getByHouse($houseId)
    {
        return response()->json(
            Meeting::where('house_id', $houseId)->orderBy('date', 'desc')->get()
        );
    }

    /**
     * [АДМИН] Создание нового собрания (с вопросами)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'house_id' => 'required|exists:houses,id',
            'date' => 'required|date',
            'questions' => 'required|array|min:1',
            'questions.*.text' => 'required|string',
            'questions.*.answers' => 'required|array|min:2',
            'questions.*.answers.*' => 'required|string'
        ]);

        try {
            DB::transaction(function () use ($data) {
                $meeting = Meeting::create([
                    'house_id' => $data['house_id'],
                    'date' => $data['date'],
                ]);

                foreach ($data['questions'] as $qData) {
                    $question = $meeting->questions()->create(['question_text' => $qData['text']]);
                    foreach ($qData['answers'] as $aText) {
                        $question->answers()->create(['answer_text' => $aText]);
                    }
                }
            });

            return response()->json(['message' => 'Собрание успешно создано', 'code' => 0]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Ошибка сохранения: ' . $e->getMessage(), 'code' => 1], 500);
        }
    }
}
