@extends('layout')

@section('title', 'Регистрация')

@section('content')
    <h1>Регистрация пользователя</h1>
    <form action="{{ route('registration.submit') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="full_name" class="form-label">ФИО:</label>
            <input type="text" name="full_name" id="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name') }}" required>
            @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email:</label>
            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Пароль:</label>
            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Подтвердите пароль:</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="house_id" class="form-label">Выберите дом:</label>
            <select name="house_id" id="house_id" class="form-select" required>
                <option value="">-- Выберите дом --</option>
                @foreach($houses as $house)
                    <option value="{{ $house->id }}">{{ $house->house_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="flat_id" class="form-label">Выберите квартиру:</label>
            <select name="flat_id" id="flat_id" class="form-select" required>
                <option value="">-- Сначала выберите дом --</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="area" class="form-label">Площадь квартиры:</label>
            <input type="number" step="0.1" name="area" id="area" class="form-control @error('area') is-invalid @enderror" value="{{ old('area') }}" required>
            @error('area') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
    </form>
    <script>
        document.getElementById('house_id').addEventListener('change', function () {
            let houseId = this.value;
            let flatSelect = document.getElementById('flat_id');
            flatSelect.innerHTML = '<option value="">-- Сначала выберите дом --</option>';
            if (houseId) {
                fetch(`/getFlats/${houseId}`)
                    .then(response => response.json())
                    .then(data => {
                        flatSelect.innerHTML = '<option value="">-- Выберите квартиру --</option>';
                        data.forEach(flat => {
                            flatSelect.innerHTML += `<option value="${flat.id}">${flat.apartment_number}</option>`;
                        });
                    })
                    .catch(error => {
                        console.error('Ошибка:', error);
                        flatSelect.innerHTML = '<option value="">Ошибка загрузки</option>';
                    });
            }
        });
    </script>
@endsection
