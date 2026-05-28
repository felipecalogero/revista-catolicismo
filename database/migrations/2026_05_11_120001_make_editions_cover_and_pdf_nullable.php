<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('editions')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE editions MODIFY cover_image VARCHAR(255) NULL');
            DB::statement('ALTER TABLE editions MODIFY pdf_file VARCHAR(255) NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE editions ALTER COLUMN cover_image DROP NOT NULL');
            DB::statement('ALTER TABLE editions ALTER COLUMN pdf_file DROP NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('editions')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE editions MODIFY cover_image VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE editions MODIFY pdf_file VARCHAR(255) NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE editions ALTER COLUMN cover_image SET NOT NULL');
            DB::statement('ALTER TABLE editions ALTER COLUMN pdf_file SET NOT NULL');
        }
    }
};
