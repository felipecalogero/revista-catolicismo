<?php

namespace App\Imports;

use App\Services\UserImport\ImportOptions;
use App\Services\UserImport\ImportResult;
use App\Services\UserImport\UserImportService;

/**
 * Adaptador fino — a lógica vive em UserImportService.
 */
class UsersImport
{
    public ImportResult $result;

    public function __construct(
        protected ImportOptions $options = new ImportOptions,
        protected ?UserImportService $service = null,
    ) {
        $this->service ??= new UserImportService;
        $this->result = new ImportResult;
    }

    public function importFile(string $path): ImportResult
    {
        return $this->result = $this->service->import($path, $this->options);
    }
}
