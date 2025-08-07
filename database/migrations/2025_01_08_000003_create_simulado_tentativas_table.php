<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('simulado_tentativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('simulado_id')->constrained()->onDelete('cascade');
            $table->json('respostas'); // Respostas do usuário
            $table->integer('pontuacao');
            $table->decimal('percentual', 5, 2);
            $table->boolean('aprovado');
            $table->timestamp('iniciado_em');
            $table->timestamp('finalizado_em')->nullable();
            $table->integer('tempo_gasto_segundos')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('simulado_tentativas');
    }
};