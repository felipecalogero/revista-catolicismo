<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FULLTEXT só faz sentido em drivers que suportam (MySQL/MariaDB/Postgres com extensão).
        // Aplicamos apenas em conexões MySQL.
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE edition_page_texts ADD FULLTEXT INDEX edition_page_texts_body_html_fulltext (body_html)');
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE edition_page_texts DROP INDEX edition_page_texts_body_html_fulltext');
    }
};
