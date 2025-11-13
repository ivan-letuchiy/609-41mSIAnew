<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Meeting;
use App\Models\Question;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;


class MeetingService
{
    public function createMeeting(int $houseId, string $date, array $questions): Meeting
    {
        try {
            $meeting = Meeting::create([
                'house_id' => $houseId,
                'date' => Carbon::parse($date),
            ]);

            foreach ($questions as $questionData) {
                $question = Question::create([
                    'meeting_id' => $meeting->id,
                    'question_text' => $questionData['text'],
                ]);

                foreach ($questionData['answers'] as $answerText) {
                    Answer::create([
                        'question_id' => $question->id,
                        'answer_text' => $answerText,
                    ]);
                }
            }

            return $meeting;
        } catch (Exception $e) {
            Log::error('Error creating meeting', ['message' => $e->getMessage()]);
            throw new Exception('Ошибка при создании собрания.');
        }
    }
}
