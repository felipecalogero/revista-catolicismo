<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        if ($this->fullTextIndexExists('edition_articles', 'edition_articles_body_html_fulltext')) {
            return;
        }

        DB::statement('ALTER TABLE edition_articles ADD FULLTEXT INDEX edition_articles_body_html_fulltext (body_html)');
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        if (! $this->fullTextIndexExists('edition_articles', 'edition_articles_body_html_fulltext')) {
            return;
        }

        DB::statement('ALTER TABLE edition_articles DROP INDEX edition_articles_body_html_fulltext');
    }

    protected function fullTextIndexExists(string $table, string $indexName): bool
    {
        $rows = DB::select('SHOW INDEX FROM '.$table.' WHERE Key_name = ?', [$indexName]);

        return $rows !== [];
    }
};
