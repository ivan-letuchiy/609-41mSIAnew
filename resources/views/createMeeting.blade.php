@extends('layout')

@section('title', 'Создать собрание')

@section('content')
    <h1>Создание собрания</h1>
    <form action="{{ route('admin.store.meeting') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="house_id" class="form-label">Выберите дом:</label>
            <select id="house_id" name="house_id" class="form-select" required>
                <option value="">-- Выберите дом --</option>
                @foreach($houses as $house)
                    <option value="{{ $house->id }}">{{ $house->house_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="date" class="form-label">Дата собрания:</label>
            <input type="date" id="date" name="date" class="form-control" required>
        </div>
        <div id="questions-container">
            <h3>Вопросы:</h3>
            <div class="question mb-3">
                <label>Текст вопроса:</label>
                <input type="text" name="questions[0][text]" class="form-control mb-2" required>
                <label>Ответы:</label>
                <div class="answers">
                    <input type="text" name="questions[0][answers][]" class="form-control mb-2" required>
                </div>
                <button type="button" class="btn btn-secondary add-answer">Добавить ответ</button>
            </div>
        </div>
        <button type="button" id="add-question" class="btn btn-secondary">Добавить вопрос</button>
        <button type="submit" class="btn btn-primary">Сохранить собрание</button>
    </form>
    <script>
        document.addEventListener('click', function (event) {
            if (event.target.id === 'add-question') {
                const container = document.getElementById('questions-container');
                const index = container.children.length;
                container.insertAdjacentHTML('beforeend', `
                    <div class="question mb-3">
                        <label>Текст вопроса:</label>
                        <input type="text" name="questions[${index}][text]" class="form-control mb-2" required>
                        <label>Ответы:</label>
                        <div class="answers">
                            <input type="text" name="questions[${index}][answers][]" class="form-control mb-2" required>
                        </div>
                        <button type="button" class="btn btn-secondary add-answer">Добавить ответ</button>
                    </div>
                `);
            } else if (event.target.classList.contains('add-answer')) {
                const answersContainer = event.target.previousElementSibling;
                const inputName = answersContainer.querySelector('input').name.replace(/\\[\\d+\\]/, '');
                answersContainer.insertAdjacentHTML('beforeend', `<input type="text" name="${inputName}" class="form-control mb-2" required>`);
            }
        });
    </script>
@endsection
