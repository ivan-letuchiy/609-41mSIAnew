@extends('layout')

@section('title', 'Результаты голосования')

@section('content')
    <div class="container">
        <h1>Результаты голосования</h1>

        <!-- Форма выбора дома и собрания -->
        <form id="resultsForm">
            @csrf
            <div class="mb-3">
                <label for="house" class="form-label">Выберите дом:</label>
                <select id="house" name="house_id" class="form-select" required>
                    <option value="">-- Выберите дом --</option>
                    @foreach ($houses as $house)
                        <option value="{{ $house->id }}">{{ $house->house_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="meeting" class="form-label">Выберите собрание:</label>
                <select id="meeting" name="meeting_id" class="form-select" required>
                    <option value="">-- Сначала выберите дом --</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Показать результаты</button>
        </form>

        <!-- Управление пагинацией -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <label for="rowsPerPage">Строк на странице:</label>
                <select id="rowsPerPage" class="form-select w-auto">
                    <option value="1">1</option>
                    <option value="5" selected>5</option>
                    <option value="10">10</option>
                </select>
            </div>
            <button id="prevPage" class="btn btn-secondary" disabled>Назад</button>
            <button id="nextPage" class="btn btn-secondary" disabled>Вперёд</button>
        </div>

        <!-- Контейнер для отображения результатов -->
        <div id="results" class="mt-4"></div>
    </div>

    <script>
        let currentPage = 1;

        // Обработка выбора дома
        document.getElementById('house').addEventListener('change', function () {
            const houseId = this.value;
            const meetingSelect = document.getElementById('meeting');
            meetingSelect.innerHTML = '<option>Загрузка...</option>';

            if (houseId) {
                fetch(`/getMeetings/${houseId}`)
                    .then(response => response.json())
                    .then(data => {
                        meetingSelect.innerHTML = '<option value="">-- Выберите собрание --</option>';
                        data.forEach(meeting => {
                            meetingSelect.innerHTML += `<option value="${meeting.id}">${meeting.date}</option>`;
                        });
                    })
                    .catch(error => {
                        console.error('Ошибка загрузки собраний:', error);
                        meetingSelect.innerHTML = '<option>Ошибка загрузки</option>';
                    });
            } else {
                meetingSelect.innerHTML = '<option value="">-- Сначала выберите дом --</option>';
            }
        });

        // Обработка формы
        document.getElementById('resultsForm').addEventListener('submit', function (e) {
            e.preventDefault();
            currentPage = 1;
            fetchResults();
        });

        // Изменение количества строк на странице
        document.getElementById('rowsPerPage').addEventListener('change', function () {
            currentPage = 1;
            fetchResults();
        });

        // Переход к предыдущей странице
        document.getElementById('prevPage').addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                fetchResults();
            }
        });

        // Переход к следующей странице
        document.getElementById('nextPage').addEventListener('click', function () {
            currentPage++;
            fetchResults();
        });

        // Получение результатов
        function fetchResults() {
            const meetingId = document.getElementById('meeting').value;
            const rowsPerPage = document.getElementById('rowsPerPage').value;

            if (!meetingId) {
                document.getElementById('results').innerHTML = '<p class="text-danger">Выберите собрание.</p>';
                return;
            }

            fetch(`{{ route('admin.voting.results.paginated') }}?meeting_id=${meetingId}&rowsPerPage=${rowsPerPage}&page=${currentPage}`, {
                method: 'GET',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        document.getElementById('results').innerHTML = `<p class="text-danger">${data.error}</p>`;
                        return;
                    }

                    // Формирование HTML для вопросов
                    let resultsHTML = `<h3>Собрание от ${data.meeting_date}</h3>`;
                    data.questions.forEach((question, index) => {
                        resultsHTML += `
                            <div class="mb-5">
                                <h4>Вопрос ${index + 1}: ${question.question}</h4>
                                <h5>Процентное соотношение</h5>
                                <ul>
                                    ${question.answers.map(answer => `<li>${answer.text}: ${answer.percentage}% (${answer.votes} голосов)</li>`).join('')}
                                </ul>
                                <h5>Участники с ответами</h5>
                                <ul>
                                    ${question.participants.map(participant => `<li>${participant.name}: ${participant.answer}</li>`).join('')}
                                </ul>
                            </div>
                        `;
                    });

                    document.getElementById('results').innerHTML = resultsHTML;

                    // Обновляем состояние кнопок пагинации
                    document.getElementById('prevPage').disabled = data.current_page === 1;
                    document.getElementById('nextPage').disabled = data.current_page === Math.ceil(data.total / data.per_page);
                })
                .catch(error => {
                    console.error('Ошибка загрузки результатов:', error);
                    document.getElementById('results').innerHTML = '<p class="text-danger">Не удалось загрузить результаты.</p>';
                });
        }
    </script>
@endsection
