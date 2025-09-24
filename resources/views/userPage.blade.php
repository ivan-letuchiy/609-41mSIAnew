@extends('layout')

@section('title', 'Личный кабинет')

@section('content')
    <h4 class="text-center text-success">Добро пожаловать, {{ $owner->full_name }}!</h4>
    <h5 class="mt-4">Проголосовать или изменить данные о квартире:</h5>
    @if ($flats->isEmpty())
        <p>Квартиры не найдены.</p>
    @else
        <div class="d-flex flex-wrap gap-3">
            @foreach ($flats as $flat)
                <a href="{{ route('user.flat', $flat->id) }}" class="btn btn-outline-primary">
                    {{ $flat->house->house_name }}, Квартира №{{ $flat->apartment_number }}
                </a>
            @endforeach
        </div>
    @endif
    <h5 class="mt-4">Добавить новую квартиру</h5>
    <a href="{{ route('user.addFlatForm') }}" class="btn btn-success">Добавить квартиру</a>
@endsection

@section('account_modal')
    <div class="modal fade" id="accountModal" tabindex="-1" aria-labelledby="accountModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="accountModalLabel">Аккаунт</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>ФИО:</strong> {{ $owner->full_name }}</p>
                    <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                    <form method="POST" action="{{ route('user.deleteAccount') }}" onsubmit="return confirm('Вы уверены, что хотите удалить аккаунт?')">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100">Удалить аккаунт</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
