<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('help_item_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('help_item_id')->constrained('help_items')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('question');
            $table->text('answer');
            $table->timestamps();

            $table->unique(['help_item_id', 'locale']);
            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('help_item_translations');
    }
};
