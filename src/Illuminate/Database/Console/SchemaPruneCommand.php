<?php

namespace Illuminate\Database\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SchemaPruneCommand extends Command
{
    protected $signature = 'schema:prune {--days=30 : Number of days to keep soft dropped tables}';
    protected $description = 'Permanently remove old soft-dropped tables';

    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoff = time() - ($days * 24 * 60 * 60);
        $tables = DB::select("SHOW TABLES LIKE 'old_%'");
        $count = 0;

        foreach ($tables as $table) {
            $tableName = current((array)$table);
            if (preg_match('/^old_.*_(\d+)$/', $tableName, $matches)) {
                $timestamp = (int)$matches[1];
                if ($timestamp < $cutoff) {
                    Schema::drop($tableName);
                    $this->info("Dropped table: {$tableName}");
                    $count++;
                }
            }
        }

        $this->info("Pruned {$count} old tables.");
    }
}