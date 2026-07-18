<?php

namespace Tests\Unit\UserImport;

use App\Services\UserImport\ColumnDetector;
use PHPUnit\Framework\TestCase;

class ColumnDetectorTest extends TestCase
{
    public function test_detects_header_format(): void
    {
        $rows = collect([
            collect(['Nome', 'E-mail', 'CPF', 'Telefone', 'UF', 'CEP', 'Produto', 'Status', 'Início', 'Fim']),
            collect(['Ana', 'ana@example.com', '39053344705', '11999999999', 'SP', '01310100', 'NOVO PREÇO - FISICO', 'active', '01/01/2026', '01/01/2027']),
            collect(['Bruno', 'bruno@example.com', '39053344706', '21988888888', 'RJ', '20040020', 'Assinatura Digital', 'active', '01/02/2026', '01/02/2027']),
            collect(['Carla', 'carla@example.com', '39053344707', '31977777777', 'MG', '30130110', 'NOVO PREÇO - FISICO', 'active', '01/03/2026', '01/03/2027']),
        ]);

        $detection = (new ColumnDetector)->detect($rows);

        $this->assertSame(ColumnDetector::FORMAT_HEADER, $detection['format']);
        $this->assertTrue($detection['has_header']);
        $this->assertSame(0, $detection['map']['name']);
        $this->assertSame(1, $detection['map']['email']);
        $this->assertSame('physical', $detection['plan_type']);
    }

    public function test_detects_original_export_format(): void
    {
        $map = (new ColumnDetector)->originalExportMap();
        $rows = collect();
        for ($i = 0; $i < 5; $i++) {
            $row = array_fill(0, 25, null);
            $row[$map['name']] = "User {$i}";
            $row[$map['email']] = "user{$i}@example.com";
            $row[$map['phone']] = '551199999'.$i.$i.$i.$i;
            $row[$map['address']] = 'Rua X';
            $row[$map['neighborhood']] = 'Centro';
            $row[$map['city']] = 'São Paulo';
            $row[$map['state']] = 'SP';
            $row[$map['zip']] = '01310100';
            $row[$map['cpf']] = '3905334470'.$i;
            $row[$map['status']] = 'active';
            $row[$map['price']] = 245.81;
            $row[$map['product_name']] = 'NOVO PREÇO - FISICO';
            $row[$map['payment_method']] = 'pix';
            $row[$map['purchase_date']] = 46158.3;
            $row[$map['current_period_start']] = 46158.3;
            $row[$map['current_period_end']] = 46523.3;
            $rows->push(collect($row));
        }

        $detection = (new ColumnDetector)->detect($rows);

        $this->assertSame(ColumnDetector::FORMAT_ORIGINAL_EXPORT, $detection['format']);
        $this->assertFalse($detection['has_header']);
        $this->assertSame('physical', $detection['plan_type']);
    }

    public function test_rejects_unrecognized_sheet(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new ColumnDetector)->detect(collect([
            collect(['foo', 'bar', 'baz']),
            collect(['1', '2', '3']),
        ]));
    }
}
