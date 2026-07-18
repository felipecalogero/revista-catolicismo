<?php

namespace App\Services\UserImport;

use Illuminate\Support\Collection;

class ColumnDetector
{
    public const FORMAT_HEADER = 'header';

    public const FORMAT_ORIGINAL_EXPORT = 'original_export';

    public function __construct(
        protected RowNormalizer $normalizer = new RowNormalizer,
    ) {}

    /**
     * @param  Collection<int, Collection<int, mixed>>  $rows
     * @return array{format: string, map: array<string, int>, has_header: bool, plan_type: string|null}
     */
    public function detect(Collection $rows): array
    {
        if ($rows->isEmpty()) {
            throw new \InvalidArgumentException('A planilha está vazia.');
        }

        $headerMap = $this->tryHeaderMap($rows->first());
        if ($headerMap !== null) {
            $sampleRows = $rows->slice(1, 15)->values();
            $planType = $this->inferPlanType($sampleRows, $headerMap);

            return [
                'format' => self::FORMAT_HEADER,
                'map' => $headerMap,
                'has_header' => true,
                'plan_type' => $planType ?? 'physical',
            ];
        }

        $originalMap = $this->tryOriginalExportMap($rows);
        if ($originalMap !== null) {
            $planType = $this->inferPlanType($rows->take(15), $originalMap);

            return [
                'format' => self::FORMAT_ORIGINAL_EXPORT,
                'map' => $originalMap,
                'has_header' => false,
                'plan_type' => $planType ?? 'physical',
            ];
        }

        throw new \InvalidArgumentException(
            'Não foi possível reconhecer o formato da planilha. Use cabeçalhos (Nome, E-mail, …) ou o export original sem cabeçalho.'
        );
    }

    /**
     * Mapa oficial do export sem cabeçalho (original.xlsx).
     *
     * @return array<string, int>
     */
    public function originalExportMap(): array
    {
        return [
            'name' => 0,
            'email' => 1,
            'phone' => 2,
            'address' => 3,
            'complement' => 4,
            'neighborhood' => 5,
            'city' => 6,
            'state' => 7,
            'zip' => 8,
            'cpf' => 9,
            'status' => 10,
            'price' => 11,
            'product_name' => 12,
            'payment_method' => 13,
            'purchase_date' => 14,
            // 15 = duplicata de purchase_date — ignorada
            'canceled_at' => 17,
            'cancel_reason' => 18,
            'current_period_start' => 19,
            'current_period_end' => 20,
            'profession' => 24,
        ];
    }

    /**
     * @return array<string, int>|null
     */
    protected function tryHeaderMap(Collection $headerRow): ?array
    {
        $map = [];
        $aliases = $this->fieldAliasGroups();

        foreach ($headerRow->values() as $i => $cell) {
            $key = $this->normalizer->normalizeKey((string) $cell);
            if ($key === '') {
                continue;
            }
            foreach ($aliases as $field => $list) {
                if (isset($map[$field])) {
                    continue;
                }
                if (in_array($key, $list, true)) {
                    $map[$field] = (int) $i;
                    break;
                }
            }
        }

        if (! isset($map['name'], $map['email'])) {
            return null;
        }

        // Se a "primeira linha" parece dado (e-mail válido), não é cabeçalho
        $emailCell = $headerRow[$map['email']] ?? null;
        if ($this->normalizer->email($emailCell) !== null) {
            return null;
        }

        return $map;
    }

    /**
     * @param  Collection<int, Collection<int, mixed>>  $rows
     * @return array<string, int>|null
     */
    protected function tryOriginalExportMap(Collection $rows): ?array
    {
        $map = $this->originalExportMap();
        $sample = $rows->take(20)->filter(function (Collection $row) use ($map) {
            return $this->normalizer->email($row[$map['email']] ?? null) !== null;
        });

        if ($sample->count() < 3) {
            return null;
        }

        $emailOk = 0;
        $ufOk = 0;
        $cepOk = 0;
        $statusOk = 0;
        $amountOk = 0;
        $dateOk = 0;

        foreach ($sample as $row) {
            if ($this->normalizer->email($row[$map['email']] ?? null)) {
                $emailOk++;
            }
            if ($this->normalizer->uf($row[$map['state']] ?? null)) {
                $ufOk++;
            }
            if ($this->normalizer->zipCode($row[$map['zip']] ?? null)) {
                $cepOk++;
            }
            if ($this->normalizer->status($row[$map['status']] ?? null)) {
                $statusOk++;
            }
            if ($this->normalizer->amount($row[$map['price']] ?? null) !== null) {
                $amountOk++;
            }
            if ($this->normalizer->date($row[$map['current_period_start']] ?? null)
                && $this->normalizer->date($row[$map['current_period_end']] ?? null)) {
                $dateOk++;
            }
        }

        $n = $sample->count();
        $ratio = fn (int $ok): float => $n > 0 ? $ok / $n : 0;

        if ($ratio($emailOk) >= 0.8
            && $ratio($ufOk) >= 0.6
            && $ratio($cepOk) >= 0.6
            && $ratio($statusOk) >= 0.7
            && $ratio($amountOk) >= 0.7
            && $ratio($dateOk) >= 0.7) {
            return $map;
        }

        return null;
    }

    /**
     * @param  Collection<int, Collection<int, mixed>>  $rows
     * @param  array<string, int>  $map
     */
    protected function inferPlanType(Collection $rows, array $map): ?string
    {
        $votes = ['physical' => 0, 'virtual' => 0];
        foreach ($rows as $row) {
            $product = $row[$map['product_name'] ?? -1] ?? null;
            $plan = $row[$map['plan_name'] ?? -1] ?? null;
            foreach ([$product, $plan] as $candidate) {
                $type = $this->normalizer->planTypeFromProduct($candidate);
                if ($type !== null) {
                    $votes[$type]++;
                }
            }
        }
        if ($votes['physical'] === 0 && $votes['virtual'] === 0) {
            return null;
        }

        return $votes['physical'] >= $votes['virtual'] ? 'physical' : 'virtual';
    }

    /**
     * @return array<string, list<string>>
     */
    protected function fieldAliasGroups(): array
    {
        return [
            'name' => ['nome', 'name', 'nomecompleto', 'assinante', 'cliente', 'customername', 'fullname'],
            'email' => ['email', 'mail', 'customeremail', 'correio'],
            'cpf' => ['cpf', 'cnpj', 'documento', 'cpfcnpj', 'customercpfcnpj', 'documentocpf'],
            'phone' => ['telefone', 'fone', 'phone', 'celular', 'cel', 'whatsapp', 'contato', 'customerphone', 'phonenumber'],
            'address' => ['endereco', 'logradouro', 'rua', 'morada', 'address', 'street', 'customeraddress'],
            'complement' => ['complemento', 'complement', 'compl'],
            'neighborhood' => ['bairro', 'distrito', 'neighborhood', 'district'],
            'city' => ['cidade', 'municipio', 'localidade', 'city'],
            'uf' => ['uf', 'sigla', 'estadosigla', 'statecode'],
            'state' => ['estado', 'estadonome', 'estadocompleto', 'state', 'statename'],
            'zip' => ['cep', 'zip', 'zipcode', 'codigopostal', 'postal', 'customerzip'],
            'start' => ['inicio', 'datainicio', 'inicioassinatura', 'start', 'startdate', 'datadeinicio', 'startedat', 'started'],
            'end' => ['fim', 'datafim', 'termino', 'validade', 'end', 'enddate', 'datafinal', 'endedat', 'ended'],
            'current_period_start' => ['currentperiodstart'],
            'current_period_end' => ['currentperiodend'],
            'purchase_date' => ['purchasedate', 'datacompra', 'lastpaymentat', 'ultimopagamento'],
            'canceled_at' => ['canceledat', 'canceladoem', 'datacancelamento', 'datadecancelamento'],
            'cancel_reason' => ['cancelreason', 'motivocancelamento', 'motivodocancelamento', 'motivo'],
            'profession' => ['profissao', 'ocupacao', 'cargo', 'profession', 'job'],
            'plan_name' => ['planname', 'plano', 'nomeplano'],
            'product_name' => ['productname', 'produto', 'nomeproduto'],
            'payment_method' => ['paymentmethod', 'formapagamento', 'metodopagamento', 'pagamento'],
            'price' => ['price', 'preco', 'valor', 'amount'],
            'status' => ['status', 'situacao', 'subscriptionstatus'],
        ];
    }
}
