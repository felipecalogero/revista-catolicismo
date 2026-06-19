<?php

namespace App\Support;

use App\Models\Edition;
use App\Models\EditionPageText;

class MagazineViewerUrl
{
    /**
     * URL do visualizador da revista na página correta, com termo para destaque.
     */
    public static function build(
        Edition $edition,
        string $term = '',
        ?int $pageNumber = null,
        ?string $pageLabel = null,
    ): string {
        $params = [];

        $page = self::resolvePageNumber($edition->id, $pageNumber, $pageLabel);
        if ($page !== null) {
            $params['page'] = $page;
        }

        $term = trim($term);
        if ($term !== '') {
            $params['q'] = $term;
        }

        $url = route('editions.magazine', $edition->slug);

        return $params === [] ? $url : $url.'?'.http_build_query($params);
    }

    public static function resolvePageNumber(int $editionId, ?int $pageNumber, ?string $pageLabel): ?int
    {
        if ($pageNumber !== null) {
            return $pageNumber;
        }

        if ($pageLabel === null || $pageLabel === '') {
            return null;
        }

        $fromPageText = EditionPageText::query()
            ->where('edition_id', $editionId)
            ->where('page_label', $pageLabel)
            ->value('page_number');

        if ($fromPageText !== null) {
            return (int) $fromPageText;
        }

        return self::pageNumberFromLabel($pageLabel);
    }

    public static function pageNumberFromLabel(?string $label): ?int
    {
        if ($label === null || $label === '') {
            return null;
        }

        if (preg_match('/^P?(\d+)/i', $label, $matches)) {
            return (int) $matches[1];
        }

        return ctype_digit($label) ? (int) $label : null;
    }
}
