@extends('layout')

@section('title', 'Вход и Регистрация')

@section('content')
    <div class="row">
        <div class="col-md-6">
            <h2>Вход</h2>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Пароль:</label>
                    <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <button type="submit" class="btn btn-primary w-100">Войти</button>
            </form>
        </div>
        <div class="col-md-6">
            <h2>Регистрация</h2>
            <a href="{{ route('registration.form') }}" class="btn btn-success w-100">Перейти к регистрации</a>
        </div>
    </div>
@endsection

