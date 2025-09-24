@extends('layout')

@section('title', 'Добавить квартиру')

@section('content')
    <h4 class="text-center text-success">Добавить квартиру</h4>
    <form action="{{ route('user.saveFlat') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="house_id" class="form-label">Выберите дом:</label>
            <select name="house_id" id="house_id" class="form-select" required>
                <option value="">-- Выберите дом --</option>
                @foreach ($houses as $house)
                    <option value="{{ $house->id }}">{{ $house->house_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="flat_id" class="form-label">Квартира:</label>
            <select name="flat_id" id="flat_id" class="form-select" required>
                <option value="">-- Сначала выберите дом --</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="area" class="form-label">Площадь:</label>
            <input type="number" step="0.1" id="area" name="area" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Сохранить</button>
    </form>

    <script>
        document.getElementById('house_id').addEventListener('change', function () {
            let houseId = this.value;
            let flatSelect = document.getElementById('flat_id');
            flatSelect.innerHTML = '<option value="">-- Загрузка... --</option>';
            if (houseId) {
                fetch(`/getFlats/${houseId}`)
                    .then(response => response.json())
                    .then(data => {
                        flatSelect.innerHTML = '<option value="">-- Выберите квартиру --</option>';
                        data.forEach(flat => {
                            flatSelect.innerHTML += `<option value="${flat.id}">Квартира №${flat.apartment_number}</option>`;
                        });
                    })
                    .catch(error => {
                        console.error('Ошибка загрузки квартир:', error);
                        flatSelect.innerHTML = '<option value="">Ошибка загрузки</option>';
                    });
            } else {
                flatSelect.innerHTML = '<option value="">-- Сначала выберите дом --</option>';
            }
        });
    </script>
@endsection

