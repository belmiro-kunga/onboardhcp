<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('perguntas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simulado_id')->constrained()->onDelete('cascade');
            $table->text('pergunta');
            $table->enum('tipo', ['multipla_escolha', 'escolha_unica']);
            $table->json('opcoes'); // Array com as opções de resposta
            $table->json('respostas_corretas'); // Array com índices das respostas corretas
            $table->text('explicacao')->nullable(); // Explicação da resposta
            $table->string('video_url')->nullable(); // Link do vídeo explicativo
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('perguntas');
    }
};