<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('institution')->nullable();
            $table->enum('type', ['checking', 'savings', 'investment', 'business']);
            $table->string('currency', 3)->default('EUR');
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->decimal('ceiling_amount', 12, 2)->nullable();
            $table->decimal('target_amount', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
