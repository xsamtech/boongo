<?php

namespace Tests\Support;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use Illuminate\Support\Facades\DB;

trait BootsWithoutMysql
{
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->afterBootstrapping(RegisterProviders::class, function (Application $app) {
            $app['config']->set('database.connections.bootstrap_tests', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
            DB::setDefaultConnection('bootstrap_tests');
            // AppServiceProvider queries these tables at boot, before test fixtures can run.
            // Use the SQL schema as the source of model column metadata for Scramble too.
            $sql = file_get_contents(__DIR__.'/../../database/boongo.sql');
            preg_match_all('/CREATE TABLE IF NOT EXISTS `([^`]+)` \((.*?)\nENGINE/s', $sql, $tables, PREG_SET_ORDER);
            foreach ($tables as $table) {
                preg_match_all('/^\s*`([^`]+)`\s+(\w+(?:\([^\n]*?\))?)([^\n]*)/m', $table[2], $columns, PREG_SET_ORDER);
                $definitions = [];
                foreach ($columns as $column) {
                    $type = match (true) {
                        $column[1] === 'id' => 'INTEGER PRIMARY KEY',
                        preg_match('/^(BIGINT|INT|TINYINT|SMALLINT)/', $column[2]) === 1 => 'INTEGER',
                        preg_match('/^(DECIMAL|DOUBLE|FLOAT)/', $column[2]) === 1 => 'NUMERIC',
                        default => 'TEXT',
                    };
                    $definitions[] = '"'.$column[1].'" '.$type;
                }
                DB::statement('CREATE TABLE "'.$table[1].'" ('.implode(', ', $definitions).')');
            }
        });
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
