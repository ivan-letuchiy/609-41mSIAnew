@extends('layout')

@section('title', 'Электронное голосование')

@section('content')
    <h1 class="text-center">Электронное голосование</h1>

    @php
        use Carbon\Carbon;use Illuminate\Support\Facades\Auth;
        $today = Carbon::now();
        $owner = Auth::user()->owner;
        $relevantMeetings = $flat->house->meetings->filter(function ($meeting) use ($owner, $today) {
            $meetingDate = Carbon::parse($meeting->date);
            $isUpcoming = $meetingDate->isFuture() || $meetingDate->isToday();
            $isRecent = $meetingDate->isPast() && $meetingDate->diffInDays($today) < 1;
            $hasNotVoted = $meeting->questions->filter(function ($question) use ($owner) {
                return !$owner->votes->contains('question_id', $question->id);
            })->isNotEmpty();

            return ($isUpcoming || $isRecent) && $hasNotVoted;
        });
    @endphp

    @if (session('success'))
        <div class="alert alert-success mt-4">{{ session('success') }}</div>
    @endif

    @if ($relevantMeetings->isEmpty())
        <p class="text-center">Нет доступных собраний для голосования.</p>
    @else
        @foreach ($relevantMeetings as $meeting)
            <h3 class="mt-4">Собрание от {{ \Carbon\Carbon::parse($meeting->date)->format('d.m.Y') }}</h3>

            <form method="POST" action="{{ route('user.vote.submit') }}">
                @csrf
                @foreach ($meeting->questions as $question)
                    <p><strong>{{ $question->question_text }}</strong></p>
                    @foreach ($question->answers as $answer)
                        <div class="form-check">
                            <input type="radio" class="form-check-input" name="answers[{{ $question->id }}]"
                                   value="{{ $answer->id }}" id="answer{{ $answer->id }}" required>
                            <label class="form-check-label"
                                   for="answer{{ $answer->id }}">{{ $answer->answer_text }}</label>
                        </div>
                    @endforeach
                @endforeach
                <button type="submit" class="btn btn-success mt-3">Проголосовать</button>
            </form>
        @endforeach
    @endif

    <h3 class="mt-5">Информация о квартире</h3>
    <p><strong>Адрес:</strong> {{ $flat->house->house_name }}</p>
    <p><strong>Номер квартиры:</strong> {{ $flat->apartment_number }}</p>
    <p><strong>Доля в
            праве:</strong> {{ $flat->owners->firstWhere('id', $owner->id)?->pivot->ownership_percentage ?? 'N/A' }}%
    </p>
    <p><strong>Площадь:</strong> {{ $flat->area }} м²</p>

    <h4 class="mt-4">Редактирование доли в праве</h4>
    <form method="POST" action="{{ route('user.flat.updateOwnership', $flat->id) }}">
        @csrf
        <div class="mb-3">
            <label for="ownership_percentage" class="form-label">Доля в праве (%)</label>
            <input type="number" step="0.01" id="ownership_percentage" name="ownership_percentage" class="form-control"
                   value="{{ $flat->owners->firstWhere('id', $owner->id)?->pivot->ownership_percentage ?? '' }}"
                   required>
        </div>
        <button type="submit" class="btn btn-primary">Сохранить изменения</button>
    </form>
@endsection
