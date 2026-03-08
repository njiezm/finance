<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['income', 'expense']);
            $table->string('label');
            $table->decimal('amount', 12, 2);
            $table->date('spent_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['type', 'spent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
