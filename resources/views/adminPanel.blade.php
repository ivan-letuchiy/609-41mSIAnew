@extends('layout')

@section('title', 'Панель администратора')

@section('content')
    <h1>Панель администратора</h1>
    <div class="d-flex flex-column gap-3">
        <a href="{{ route('admin.create.house') }}" class="btn btn-primary">Создать новый дом</a>
        <a href="{{ route('admin.create.meeting') }}" class="btn btn-primary">Создать новое собрание</a>
        <a href="{{ route('admin.voting.results') }}" class="btn btn-primary">Результаты голосования</a>
    </div>
@endsection
