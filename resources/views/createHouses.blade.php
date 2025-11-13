@extends('layout')

@section('title', 'Создать дом')

@section('content')
    <h1>Создание нового дома</h1>
    <form action="{{ route('admin.store.house') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="house_name" class="form-label">Название дома:</label>
            <input type="text" id="house_name" name="house_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="start_apartment" class="form-label">Начальный номер квартиры:</label>
            <input type="number" id="start_apartment" name="start_apartment" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="end_apartment" class="form-label">Конечный номер квартиры:</label>
            <input type="number" id="end_apartment" name="end_apartment" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Создать</button>
    </form>
@endsection
