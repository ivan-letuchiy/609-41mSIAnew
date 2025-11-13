<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Проверка, существует ли таблица 'users'
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // Колонка 'name'
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('role')->default('user'); // Роль пользователя: 'user' или 'admin'
                $table->rememberToken();
                $table->timestamps();
            });
        }

        // Проверка, существует ли таблица 'owners'
        if (Schema::hasTable('owners')) {
            // Добавление внешнего ключа после создания таблицы
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('name')  // Колонка 'name' в таблице 'users'
                ->references('full_name') // Ссылается на колонку 'full_name' в таблице 'owners'
                ->on('owners') // Таблица 'owners'
                ->onDelete('cascade')  // При удалении владельца, все связанные пользователи тоже будут удалены
                ->onUpdate('cascade'); // При обновлении данных владельца, обновятся все связанные пользователи
            });
        }

        // Создание таблицы для сброса пароля
        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        // Создание таблицы для сессий
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
