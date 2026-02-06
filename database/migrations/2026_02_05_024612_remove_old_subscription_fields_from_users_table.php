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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_active',
                'subscription_start_date',
                'subscription_end_date',
                'subscription_plan',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('subscription_active')->default(false)->after('role');
            $table->date('subscription_start_date')->nullable()->after('subscription_active');
            $table->date('subscription_end_date')->nullable()->after('subscription_start_date');
            $table->string('subscription_plan')->nullable()->after('subscription_end_date');
        });
    }
};
