<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            // Добавляем колонку для ссылки на картинку
            $table->string('picture_url')->nullable()->after('house_name');
        });
    }

    public function down(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            // Удаляем колонку при откате
            $table->dropColumn('picture_url');
        });
    }
};
