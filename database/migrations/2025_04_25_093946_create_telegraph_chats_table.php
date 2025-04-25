<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('telegraph_chats', function (Blueprint $table): void {
            $table->id();
            $table->string('chat_id');
            $table->string('name')->nullable();

            $table->foreignId('telegraph_bot_id')->constrained('telegraph_bots')->cascadeOnDelete();
            $table->timestamps();
            $table->string('state', 100)->default(\App\Enums\ChatStateEnum::ACTIVE);
            $table->json('context')->nullable();

            $table->unique(['chat_id', 'telegraph_bot_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegraph_chats');
    }
};
