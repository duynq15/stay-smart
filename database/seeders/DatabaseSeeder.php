<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $sqlPath = base_path('_reference/staysmart_seed.sql');

        if (! file_exists($sqlPath)) {
            $this->command->error("Seed file not found: {$sqlPath}");

            return;
        }

        $raw = file_get_contents($sqlPath);

        $marker = '-- DATA';
        $pos = strpos($raw, $marker);
        if ($pos === false) {
            $this->command->error('Could not locate -- DATA section in seed file.');

            return;
        }

        $dataSection = substr($raw, $pos);

        $sql = $this->prepareForSqlite($dataSection);

        $statements = $this->splitStatements($sql);

        DB::connection()->disableQueryLog();

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement === '') {
                continue;
            }
            DB::statement($statement);
        }

        $this->command->info('Seeded ' . count($statements) . ' SQL statements from staysmart_seed.sql');

        // Phase 2: bổ sung 22 KS cho 20 scenario chatbot
        $this->call(Phase2HotelSeeder::class);
    }

    private function prepareForSqlite(string $sql): string
    {
        $lines = preg_split('/\r?\n/', $sql);
        $kept = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                continue;
            }
            if (preg_match('/^(SET\s|DROP\s|CREATE\s|\)\s*ENGINE)/i', $trimmed)) {
                continue;
            }
            $kept[] = $line;
        }
        $cleaned = implode("\n", $kept);

        return str_replace('`', '', $cleaned);
    }

    private function splitStatements(string $sql): array
    {
        $statements = [];
        $buffer = '';
        $inSingle = false;
        $len = strlen($sql);

        for ($i = 0; $i < $len; $i++) {
            $char = $sql[$i];

            if ($char === "'") {
                if ($inSingle && $i + 1 < $len && $sql[$i + 1] === "'") {
                    $buffer .= "''";
                    $i++;
                    continue;
                }
                $inSingle = ! $inSingle;
                $buffer .= $char;
                continue;
            }

            if ($char === ';' && ! $inSingle) {
                $statements[] = $buffer;
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        if (trim($buffer) !== '') {
            $statements[] = $buffer;
        }

        return $statements;
    }
}
