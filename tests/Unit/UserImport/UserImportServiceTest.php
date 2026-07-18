<?php

namespace Tests\Unit\UserImport;

use App\Services\UserImport\ImportOptions;
use App\Services\UserImport\UserImportService;
use PHPUnit\Framework\TestCase;

class UserImportServiceTest extends TestCase
{
    public function test_preview_header_csv_maps_rows(): void
    {
        $path = dirname(__DIR__, 2).'/fixtures/users-import/with-header.csv';
        $service = new UserImportService;
        $preview = $service->preview($path, new ImportOptions(dryRun: true));

        $this->assertSame('header', $preview['detection']['format']);
        $this->assertCount(3, $preview['rows']);

        $ana = $preview['rows'][0];
        $this->assertSame('ana.teste@import.local', $ana['user']['email']);
        $this->assertSame('physical', $ana['subscription']['plan_type']);
        $this->assertSame('São Paulo', $ana['user']['city']);

        $bruno = $preview['rows'][1];
        $this->assertSame('virtual', $bruno['subscription']['plan_type']);

        $cancel = $preview['rows'][2];
        $this->assertSame('cancelled', $cancel['subscription']['status']);
    }

    public function test_preview_original_format_xlsx(): void
    {
        $path = dirname(__DIR__, 2).'/fixtures/users-import/original-format.xlsx';
        $service = new UserImportService;
        $preview = $service->preview($path);

        $this->assertSame('original_export', $preview['detection']['format']);
        $this->assertCount(3, $preview['rows']);

        $first = $preview['rows'][0];
        $this->assertSame('maria.original@import.local', $first['user']['email']);
        $this->assertSame('SP', $first['user']['state']);
        $this->assertSame('Campinas', $first['user']['city']);
        $this->assertSame('11987654321', $first['user']['phone']);
        $this->assertNull($first['user']['complement']); // faixa ViaCEP ignorada
        $this->assertSame('active', $first['subscription']['status']);

        $cancelled = $preview['rows'][2];
        $this->assertSame('cancelled', $cancelled['subscription']['status']);
        $this->assertSame('Cancelado pelo comprador', $cancelled['subscription']['cancel_reason']);
    }

    public function test_dry_run_counts_without_requiring_database_writes(): void
    {
        $path = dirname(__DIR__, 2).'/fixtures/users-import/with-header.csv';
        $result = (new UserImportService)->import($path, new ImportOptions(dryRun: true));

        $this->assertSame(3, $result->created);
        $this->assertSame(0, $result->updated);
        $this->assertSame(0, $result->failed);
        $this->assertSame('header', $result->detectedFormat);
    }
}
