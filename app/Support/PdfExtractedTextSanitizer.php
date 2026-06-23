<?php

namespace App\Support;

/**
 * Corrige lixo de ligaduras que o smalot/pdfparser emite em PDFs com fontes embutidas.
 *
 * Ex.: "f&#6684777;m" → "fim", "in&#6684780;uência" → "influência".
 */
class PdfExtractedTextSanitizer
{
    /**
     * Entidades numéricas inválidas (&gt; U+10FFFF) mapeadas para o par de letras correto.
     *
     * @var array<string, string>
     */
    private const LIGATURE_ENTITY_MAP = [
        '6684777' => 'fi',
        '6684780' => 'fl',
        '6684774' => 'ff',
    ];

    /**
     * @var array<string, string>
     */
    private const UNICODE_LIGATURE_MAP = [
        "\u{FB00}" => 'ff',
        "\u{FB01}" => 'fi',
        "\u{FB02}" => 'fl',
        "\u{FB03}" => 'ffi',
        "\u{FB04}" => 'ffl',
        "\u{FB05}" => 'ft',
        "\u{FB06}" => 'st',
    ];

    public static function sanitize(string $text): string
    {
        // Texto já salvo com e() pode ter &amp;#6684777; — decodifica uma vez.
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = strtr($text, self::UNICODE_LIGATURE_MAP);

        $text = preg_replace_callback(
            '/&#(\d+);/',
            function (array $match): string {
                $code = $match[1];

                if (isset(self::LIGATURE_ENTITY_MAP[$code])) {
                    return self::LIGATURE_ENTITY_MAP[$code];
                }

                if ((int) $code > 0x10FFFF) {
                    return '';
                }

                return $match[0];
            },
            $text
        );

        return $text ?? '';
    }
}
