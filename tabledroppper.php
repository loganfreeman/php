<?php

namespace Spatie\MigrateFresh\TableDroppers;

use DB;
use Schema;
use stdClass;

class Mysql implements TableDropper
{
    public function dropAllTables()
    {
        Schema::disableForeignKeyConstraints();

        collect(DB::select('SHOW TABLES'))
            ->map(function (stdClass $tableProperties) {
                return get_object_vars($tableProperties)[key($tableProperties)];
            })
            ->each(function (string $tableName) {
                Schema::drop($tableName);
            });

        Schema::enableForeignKeyConstraints();
    }
}


class Pgsql implements TableDropper
{
    public function dropAllTables()
    {
        $tableNames = $this->getTableNames();

        if ($tableNames->isEmpty()) {
            return;
        }

        DB::statement("DROP TABLE {$tableNames->implode(',')} CASCADE");
    }

    /**
     * Get a list of all tables in the schema.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getTableNames()
    {
        return collect(
            DB::select('SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = ?', [DB::getConfig('schema')])
        )->pluck('tablename');
    }
}
