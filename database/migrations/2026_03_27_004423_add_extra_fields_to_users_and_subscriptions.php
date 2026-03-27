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
            $table->string('address_number')->nullable()->after('address');
            $table->string('complement')->nullable()->after('address_number');
            $table->string('profession')->nullable()->after('zip_code');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('plan_name')->nullable()->after('plan_type');
            $table->string('product_name')->nullable()->after('plan_name');
            $table->timestamp('canceled_at')->nullable()->after('end_date');
            $table->text('cancel_reason')->nullable()->after('canceled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['address_number', 'complement', 'profession']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['plan_name', 'product_name', 'canceled_at', 'cancel_reason']);
        });
    }
};
