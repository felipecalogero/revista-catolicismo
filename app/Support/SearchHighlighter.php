<?php

namespace App\Support;

/**
 * Utilitários compartilhados para busca textual: snippets com destaque,
 * FULLTEXT BOOLEAN MODE e normalização accent-insensitive.
 */
class SearchHighlighter
{
    public function likePattern(string $search): string
    {
        return '%'.addcslashes(trim($search), '%_\\').'%';
    }

    public function buildFullTextQuery(string $search): string
    {
        return $this->buildFullTextBooleanQuery($search, prefixWildcard: true);
    }

    /** FULLTEXT sem wildcard: exige o token inteiro (não casa substrings). */
    public function buildFullTextExactQuery(string $search): string
    {
        return $this->buildFullTextBooleanQuery($search, prefixWildcard: false);
    }

    /** @return array<int, string> */
    public function searchableWords(string $search): array
    {
        $sanitized = preg_replace('/[+\-><()~*"@]/u', ' ', $search);
        $sanitized = trim(preg_replace('/\s+/u', ' ', (string) $sanitized));
        if ($sanitized === '') {
            return [];
        }

        return array_values(array_filter(
            explode(' ', $sanitized),
            fn ($w) => mb_strlen($w) >= 3
        ));
    }

    protected function buildFullTextBooleanQuery(string $search, bool $prefixWildcard): string
    {
        $words = $this->searchableWords($search);
        if ($words === []) {
            return '';
        }

        return implode(' ', array_map(
            fn ($w) => '+'.$w.($prefixWildcard ? '*' : ''),
            $words
        ));
    }

    /**
     * Filtra colunas de texto longo exigindo palavra inteira via LIKE.
     *
     * Evita REGEXP com [[:<:]] (removido no MySQL 8+) e evita que "ações"
     * case dentro de "informações" (exige espaço, pontuação ou tag HTML antes).
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    public function applyWholeWordContentWhere($query, string $column, string $term): void
    {
        $words = $this->searchableWords($term);
        if ($words === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        foreach ($words as $word) {
            $query->where(function ($q) use ($column, $word) {
                $this->applyWholeWordLikeOr($q, $column, $word);
            });
        }
    }

    /**
     * Busca em body_html: FULLTEXT no MySQL (rápido) ou LIKE por palavra inteira.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    public function applyBodyHtmlSearchWhere($query, string $column, string $term): void
    {
        if ($this->canUseMysqlFullText($term)) {
            $query->whereRaw(
                "MATCH({$column}) AGAINST (? IN BOOLEAN MODE)",
                [$this->buildFullTextExactQuery($term)]
            );

            return;
        }

        $this->applyWholeWordContentWhere($query, $column, $term);
    }

    public function canUseMysqlFullText(string $term): bool
    {
        return \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'mysql'
            && $this->buildFullTextExactQuery($term) !== '';
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    protected function applyWholeWordLikeOr($query, string $column, string $word): void
    {
        $e = addcslashes($word, '%_\\');

        $query->where($column, 'like', $e)
            ->orWhere($column, 'like', $e.' %')
            ->orWhere($column, 'like', '% '.$e.' %')
            ->orWhere($column, 'like', '% '.$e)
            ->orWhere($column, 'like', '%>'.$e.'<%')
            ->orWhere($column, 'like', '%>'.$e.' %')
            ->orWhere($column, 'like', '% '.$e.'</%');

        foreach (['.', ',', ';', ':', '!', '?', '"', ')', '-', '»', '«', '…', '(', '/'] as $punct) {
            $pe = addcslashes($punct, '%_\\');
            $query->orWhere($column, 'like', '% '.$e.$pe.'%')
                ->orWhere($column, 'like', $e.$pe.'%')
                ->orWhere($column, 'like', '%>'.$e.$pe.'%');
        }
    }

    public function buildSnippet(string $html, string $term, int $contextChars = 140): string
    {
        $plain = trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $plain = $this->stripInvalidCharRefs($plain);
        $plain = preg_replace('/\s+/u', ' ', $plain) ?? $plain;
        if ($plain === '') {
            return '';
        }

        $haystackNorm = $this->normalizeForSearch($plain);
        $words = array_values(array_filter(
            preg_split('/\s+/u', trim($term)) ?: [],
            fn ($w) => mb_strlen($this->normalizeForSearch($w)) >= 3
        ));

        $pos = false;
        $matchLen = 0;
        foreach ($words as $w) {
            $needle = $this->normalizeForSearch($w);
            $found = $this->findWordStart($haystackNorm, $needle, 0);
            if ($found !== false) {
                $pos = $found;
                $matchLen = $this->wordRunLength($haystackNorm, $found, mb_strlen($needle));
                break;
            }
        }

        if ($pos === false) {
            $head = mb_substr($plain, 0, $contextChars * 2);
            $head = e($head);
            if (mb_strlen($plain) > $contextChars * 2) {
                $head .= '…';
            }

            return $head;
        }

        $start = max(0, $pos - $contextChars);
        $length = $matchLen + ($contextChars * 2);
        $snippet = mb_substr($plain, $start, $length);

        if ($start > 0) {
            $firstSpace = mb_strpos($snippet, ' ');
            if ($firstSpace !== false && $firstSpace < 30) {
                $snippet = mb_substr($snippet, $firstSpace + 1);
            }
        }
        $endsAtText = ($start + $length) >= mb_strlen($plain);
        if (! $endsAtText) {
            $lastSpace = mb_strrpos($snippet, ' ');
            if ($lastSpace !== false && (mb_strlen($snippet) - $lastSpace) < 30) {
                $snippet = mb_substr($snippet, 0, $lastSpace);
            }
        }

        $highlighted = $this->highlightTermsInText($snippet, $words);
        $prefix = $start > 0 ? '…' : '';
        $suffix = $endsAtText ? '' : '…';

        return $prefix.$highlighted.$suffix;
    }

    /**
     * @param  array<int, string>  $words
     */
    public function highlightTermsInText(string $text, array $words): string
    {
        $textNorm = $this->normalizeForSearch($text);
        $ranges = [];

        foreach ($words as $w) {
            $needle = $this->normalizeForSearch($w);
            if (mb_strlen($needle) < 3) {
                continue;
            }
            $needleLen = mb_strlen($needle);
            $offset = 0;
            while (($p = $this->findWordStart($textNorm, $needle, $offset)) !== false) {
                $runLen = $this->wordRunLength($textNorm, $p, $needleLen);
                $ranges[] = [$p, $runLen];
                $offset = $p + $runLen;
            }
        }

        if ($ranges === []) {
            return e($text);
        }

        usort($ranges, fn ($a, $b) => $a[0] <=> $b[0]);
        $merged = [];
        foreach ($ranges as $r) {
            if ($merged === []) {
                $merged[] = $r;

                continue;
            }
            $last = &$merged[count($merged) - 1];
            if ($r[0] <= $last[0] + $last[1]) {
                $last[1] = max($last[1], ($r[0] + $r[1]) - $last[0]);
            } else {
                $merged[] = $r;
            }
            unset($last);
        }

        $out = '';
        $cursor = 0;
        foreach ($merged as [$p, $len]) {
            $out .= e(mb_substr($text, $cursor, $p - $cursor));
            $out .= '<mark>'.e(mb_substr($text, $p, $len)).'</mark>';
            $cursor = $p + $len;
        }
        $out .= e(mb_substr($text, $cursor));

        return $out;
    }

    public function stripInvalidCharRefs(string $text): string
    {
        return preg_replace_callback(
            '/&#(\d+);/',
            fn ($m) => ((int) $m[1]) > 0x10FFFF ? '' : $m[0],
            $text
        ) ?? $text;
    }

    public function normalizeForSearch(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = strtr($text, [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n',
        ]);

        return $text;
    }

    public function findWordStart(string $haystack, string $needle, int $offset)
    {
        if ($needle === '') {
            return false;
        }
        while (($p = mb_strpos($haystack, $needle, $offset)) !== false) {
            if ($p === 0) {
                return $p;
            }
            $prev = mb_substr($haystack, $p - 1, 1);
            if (! preg_match('/[a-z0-9]/i', $prev)) {
                return $p;
            }
            $offset = $p + 1;
        }

        return false;
    }

    public function wordRunLength(string $haystack, int $start, int $minLen): int
    {
        $len = mb_strlen($haystack);
        $end = $start + $minLen;
        while ($end < $len) {
            $ch = mb_substr($haystack, $end, 1);
            if (! preg_match('/[a-z0-9]/i', $ch)) {
                break;
            }
            $end++;
        }

        return $end - $start;
    }
}
