<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('goals', function (Blueprint $table): void {
            $table->enum('cadence', ['monthly', 'quarterly', 'semiannual', 'annual'])->default('monthly')->after('type');
            $table->date('start_date')->nullable()->after('current_amount');
        });
    }

    public function down(): void
    {
        Schema::table('goals', function (Blueprint $table): void {
            $table->dropColumn(['cadence', 'start_date']);
        });
    }
};
