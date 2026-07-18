<?php

namespace App\Services\UserImport;

class ImportOptions
{
    public function __construct(
        public bool $updateExisting = true,
        public bool $extendExpiredVigence = false,
        public bool $dryRun = false,
    ) {}
}
