<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\DB;

trait ImportsCsvDataset
{
    /**
     * Bulk import a pre-generated CSV dataset file into a database table using LOAD DATA LOCAL INFILE.
     * ZERO loops in the seeder code!
     */
    protected function importCsv(string $table, string $csvPath, ?string $columns = null, bool $ignore = false): int
    {
        if (! file_exists($csvPath)) {
            throw new \RuntimeException("File dataset tidak ditemukan: {$csvPath}");
        }

        $normalizedPath = str_replace('\\', '/', realpath($csvPath) ?: $csvPath);

        $fp = fopen($csvPath, 'r');
        $firstLine = fgets($fp);
        fclose($fp);

        $lineTerminator = str_contains((string) $firstLine, "\r") ? '\r\n' : '\n';
        $rawColumns = trim((string) $firstLine);
        $escapedColumns = implode(',', array_map(fn ($col) => '`'.trim($col, "` \t\n\r\0\x0B").'`', explode(',', $rawColumns)));

        $ignoreClause = $ignore ? 'IGNORE ' : '';

        $pdo = DB::connection()->getPdo();
        $sql = "LOAD DATA LOCAL INFILE '{$normalizedPath}'
                {$ignoreClause}INTO TABLE `{$table}`
                FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' ESCAPED BY '\\\\'
                LINES TERMINATED BY '{$lineTerminator}'
                IGNORE 1 LINES
                ({$escapedColumns})";

        return (int) $pdo->exec($sql);
    }
}
