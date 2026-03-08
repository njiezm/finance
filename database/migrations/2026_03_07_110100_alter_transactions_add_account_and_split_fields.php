<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->foreignId('account_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->uuid('split_group')->nullable()->after('notes');
            $table->unsignedSmallInteger('split_number')->nullable()->after('split_group');
            $table->unsignedSmallInteger('split_total')->nullable()->after('split_number');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('account_id');
            $table->dropColumn(['split_group', 'split_number', 'split_total']);
        });
    }
};
