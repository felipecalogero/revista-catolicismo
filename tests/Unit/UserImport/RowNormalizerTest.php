<?php

namespace Tests\Unit\UserImport;

use App\Services\UserImport\RowNormalizer;
use PHPUnit\Framework\TestCase;

class RowNormalizerTest extends TestCase
{
    private RowNormalizer $n;

    protected function setUp(): void
    {
        parent::setUp();
        $this->n = new RowNormalizer;
    }

    public function test_pads_cpf_and_cep_leading_zeros(): void
    {
        $this->assertSame('09232918900', $this->n->cpfOrCnpj(9232918900));
        $this->assertSame('06740504', $this->n->zipCode(6740504));
    }

    public function test_strips_brazil_phone_country_code(): void
    {
        $this->assertSame('62986549433', $this->n->phone('5562986549433'));
        $this->assertSame('11974839658', $this->n->phone(5511974839658));
    }

    public function test_status_and_plan_type(): void
    {
        $this->assertSame('cancelled', $this->n->status('canceled'));
        $this->assertSame('active', $this->n->status('ativo'));
        $this->assertSame('physical', $this->n->planTypeFromProduct('NOVO PREÇO - FISICO'));
        $this->assertSame('virtual', $this->n->planTypeFromProduct('Assinatura Digital'));
    }

    public function test_cleans_invisible_chars_and_slashes(): void
    {
        $dirty = "Rua Teste\u{2060} 10 \\/ apto";
        $this->assertSame('Rua Teste 10 / apto', $this->n->cleanText($dirty));
    }

    public function test_email_validation(): void
    {
        $this->assertSame('ana@example.com', $this->n->email('Ana@Example.com'));
        $this->assertNull($this->n->email('Customer Email'));
        $this->assertNull($this->n->email('not-an-email'));
    }
}
