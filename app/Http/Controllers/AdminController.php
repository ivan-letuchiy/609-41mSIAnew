<?php

namespace App\Http\Controllers;

use App\Models\{House, Meeting};
use App\Services\{HouseService, MeetingService};
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    private HouseService $houseService;
    private MeetingService $meetingService;

    public function __construct(HouseService $houseService, MeetingService $meetingService)
    {
        $this->houseService = $houseService;
        $this->meetingService = $meetingService;
    }

    public function index(): View
    {
        return view('adminPanel');
    }

    public function create(): View
    {
        return view('createHouses');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'house_name' => 'required|string|max:255|unique:houses,house_name',
            'start_apartment' => 'required|integer|min:1',
            'end_apartment' => 'required|integer|gt:start_apartment',
        ]);

        try {
            $this->houseService->createHouseWithFlats(
                $validated['house_name'],
                $validated['start_apartment'],
                $validated['end_apartment']
            );
            return redirect()->route('admin.panel')->with('success', 'Дом создан успешно!');
        } catch (\Exception $e) {
            return redirect()->route('admin.panel')->withErrors('Ошибка при создании дома: ' . $e->getMessage());
        }
    }

    public function createMeeting(): View
    {
        $houses = House::all();
        return view('createMeeting', compact('houses'));
    }

    public function storeMeeting(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'house_id' => 'required|exists:houses,id',
            'date' => 'required|date',
            'questions' => 'required|array',
            'questions.*.text' => 'required|string',
            'questions.*.answers' => 'required|array|min:1',
            'questions.*.answers.*' => 'required|string',
        ]);

        try {
            $this->meetingService->createMeeting(
                $validated['house_id'],
                $validated['date'],
                $validated['questions']
            );
            return redirect()->route('admin.panel')->with('success', 'Собрание создано успешно!');
        } catch (\Exception $e) {
            return redirect()->route('admin.panel')->withErrors('Ошибка при создании собрания: ' . $e->getMessage());
        }
    }

    public function getVotingResults(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Валидация входных данных
            $validated = $request->validate([
                'house_id' => 'required|exists:houses,id',
                'meeting_id' => 'required|exists:meetings,id',
            ]);

            // Загрузка собрания с вопросами, ответами и голосами
            $meeting = Meeting::with(['questions.answers.votes.owner'])->find($validated['meeting_id']);

            if (!$meeting) {
                return response()->json(['error' => 'Собрание не найдено'], 404);
            }

            // Формирование результатов для каждого вопроса
            $questionsData = $meeting->questions->map(function ($question) {
                $totalVotes = $question->votes->count();

                // Ответы и их процентное соотношение
                $answers = $question->answers->map(function ($answer) use ($totalVotes) {
                    $voteCount = $answer->votes->count();
                    $percentage = $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100, 2) : 0;
                    return [
                        'id' => $answer->id,
                        'text' => $answer->answer_text,
                        'percentage' => $percentage,
                        'votes' => $voteCount,
                    ];
                });

                // Участники, проголосовавшие по этому вопросу
                $participants = $question->votes->map(function ($vote) {
                    return [
                        'name' => $vote->owner->full_name,
                        'answer' => $vote->question->answers->firstWhere('id', $vote->vote_answer)?->answer_text ?? 'Неизвестно',
                    ];
                });

                return [
                    'question' => $question->question_text,
                    'answers' => $answers,
                    'participants' => $participants,
                ];
            });

            return response()->json([
                'meeting_date' => $meeting->date,
                'questions' => $questionsData,
            ]);

        } catch (\Exception $e) {
            Log::error('Ошибка получения результатов голосования: ' . $e->getMessage());
            return response()->json(['error' => 'Произошла ошибка при обработке данных'], 500);
        }
    }

    public function getMeetings($houseId): \Illuminate\Http\JsonResponse
    {
        $meetings = Meeting::where('house_id', $houseId)->select('id', 'date')->get();
        return response()->json($meetings);
    }

    public function showVotingResults(): View
    {
        $houses = House::all(); // Загружаем список домов для выбора
        return view('votingResults', compact('houses'));
    }

    public function getPaginatedResults(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'meeting_id' => 'required|exists:meetings,id',
            'rowsPerPage' => 'integer|min:1|max:50',
            'page' => 'integer|min:1',
        ]);

        $rowsPerPage = $validated['rowsPerPage'] ?? 5;
        $page = $validated['page'] ?? 1;

        $meeting = Meeting::with(['questions.answers.votes.owner'])->find($validated['meeting_id']);

        if (!$meeting) {
            return response()->json(['error' => 'Собрание не найдено'], 404);
        }

        $questions = $meeting->questions()->paginate($rowsPerPage, ['*'], 'page', $page);

        $formattedQuestions = $questions->map(function ($question) {
            $totalVotes = $question->votes->count();
            $answers = $question->answers->map(function ($answer) use ($totalVotes) {
                $voteCount = $answer->votes->count();
                $percentage = $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100, 2) : 0;
                return [
                    'id' => $answer->id,
                    'text' => $answer->answer_text,
                    'percentage' => $percentage,
                    'votes' => $voteCount,
                ];
            });

            $participants = $question->votes->map(function ($vote) {
                return [
                    'name' => $vote->owner->full_name,
                    'answer' => $vote->question->answers->firstWhere('id', $vote->vote_answer)?->answer_text ?? 'Неизвестно',
                ];
            });

            return [
                'question' => $question->question_text,
                'answers' => $answers,
                'participants' => $participants,
            ];
        });

        return response()->json([
            'meeting_date' => $meeting->date,
            'questions' => $formattedQuestions,
            'current_page' => $questions->currentPage(),
            'per_page' => $questions->perPage(),
            'total' => $questions->total(),
        ]);
    }

}
