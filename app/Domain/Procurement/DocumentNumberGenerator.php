<?php

declare(strict_types=1);

namespace App\Domain\Procurement;

use Illuminate\Support\Facades\DB;

class DocumentNumberGenerator
{
    /**
     * Generate sequential document reference number.
     * Example: PR-2026-000001, PO-2026-000001, GRN-2026-000001
     */
    public function generate(string $prefix, string $table, string $column): string
    {
        $year = date('Y');
        $prefixGroup = "{$prefix}-{$year}-";

        $lastDoc = DB::table($table)
            ->where($column, 'like', "{$prefixGroup}%")
            ->orderByDesc('id')
            ->value($column);

        if (!$lastDoc) {
            $nextSeq = 1;
        } else {
            $parts = explode('-', $lastDoc);
            $nextSeq = isset($parts[2]) ? ((int) $parts[2]) + 1 : 1;
        }

        return sprintf('%s-%s-%06d', $prefix, $year, $nextSeq);
    }
}
