<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Управление домами')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="javascript:history.back()">
            <i class="bi bi-arrow-left"></i>
        </a>
        @auth
            <a class="navbar-brand" href="{{ Auth::user()->role === 'admin' ? route('admin.panel') : route('user.page') }}">
                <i class="bi bi-house-door"></i>
            </a>
        @else
            <a class="navbar-brand" href="{{ route('main') }}">
                <i class="bi bi-house-door"></i>
            </a>
        @endauth
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                @auth
                    <li class="nav-item">
                        <button class="btn btn-link nav-link" data-bs-toggle="modal" data-bs-target="#accountModal">
                            <i class="bi bi-person-circle"></i>
                        </button>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-link nav-link">Выйти</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Войти</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
<div class="container mt-4">
    @include('partials.messages')
    @yield('content')
</div>

<!-- Модальное окно для аккаунта -->
<div class="modal fade" id="accountModal" tabindex="-1" aria-labelledby="accountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="accountModalLabel">Аккаунт</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>ФИО:</strong> {{ Auth::user()->owner->full_name ?? '' }}</p>
                <p><strong>Email:</strong> {{ Auth::user()->email ?? '' }}</p>
                <form method="POST" action="{{ route('user.deleteAccount') }}" onsubmit="return confirm('Вы уверены, что хотите удалить аккаунт?')">
                    @csrf
                    <button type="submit" class="btn btn-danger w-100">Удалить аккаунт</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const accountModal = document.getElementById('accountModal');
        accountModal.addEventListener('hidden.bs.modal', function() {
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
        });
    });
</script>
</body>
</html>
