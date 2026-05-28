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
        Schema::table('editions', function (Blueprint $table) {
            $table->boolean('is_legacy')->default(false)->after('views');
            $table->unsignedInteger('legacy_issue_number')->nullable()->after('is_legacy');
            $table->longText('table_of_contents')->nullable()->after('legacy_issue_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('editions', function (Blueprint $table) {
            $table->dropColumn(['is_legacy', 'legacy_issue_number', 'table_of_contents']);
        });
    }
};
