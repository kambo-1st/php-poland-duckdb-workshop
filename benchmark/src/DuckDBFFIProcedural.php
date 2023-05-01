<?php declare(strict_types=1);

namespace Kambo\DuckDB;

use FFI;

/**
 *
 */
class DuckDBFFIProcedural
{
    private $duckDbFFI;

    public function getData() : array {
        $duckDbFFI = $this->duckDbFFI;

        $database   = $duckDbFFI->new("duckdb_database");
        $connection = $duckDbFFI->new("duckdb_connection");

        $result = $duckDbFFI->duckdb_open(null, FFI::addr($database));

        if ($result === $duckDbFFI->DuckDBError) {
            $duckDbFFI->duckdb_disconnect(FFI::addr($connection));
            $duckDbFFI->duckdb_close(FFI::addr($database));
            throw new Exception('Cannot open database');
        }

        $result = $duckDbFFI->duckdb_connect($database, FFI::addr($connection));
        if ($result === $duckDbFFI->DuckDBError) {
            $duckDbFFI->duckdb_disconnect(FFI::addr($connection));
            $duckDbFFI->duckdb_close(FFI::addr($database));
            throw new Exception('Cannot connect to database');
        }

        $result = $duckDbFFI->duckdb_query($connection, 'CREATE TABLE integers(i INTEGER, j INTEGER);', null);
        if ($result === $duckDbFFI->DuckDBError) {
            $duckDbFFI->duckdb_disconnect(FFI::addr($connection));
            $duckDbFFI->duckdb_close(FFI::addr($database));
            throw new Exception('Cannot execute query');
        }

        $result = $duckDbFFI->duckdb_query($connection, 'INSERT INTO integers VALUES (3,4), (5,6), (7, NULL) ', null);
        if ($result === $duckDbFFI->DuckDBError) {
            $duckDbFFI->duckdb_disconnect(FFI::addr($connection));
            $duckDbFFI->duckdb_close(FFI::addr($database));
            throw new Exception('Cannot execute query');
        }

        $queryResult = $duckDbFFI->new('duckdb_result');

        $result = $duckDbFFI->duckdb_query($connection, 'SELECT * FROM integers; ', FFI::addr($queryResult));

        if ($result === $duckDbFFI->DuckDBError) {
            $error = "Error in query: ".$duckDbFFI->duckdb_result_error(FFI::addr($queryResult));

            $duckDbFFI->duckdb_destroy_result(FFI::addr($queryResult));
            $duckDbFFI->duckdb_disconnect(FFI::addr($connection));
            $duckDbFFI->duckdb_close(FFI::addr($database));
            throw new Exception($error);
        }

        $rowCount = $duckDbFFI->duckdb_row_count(FFI::addr($queryResult));
        $columnCount = $duckDbFFI->duckdb_column_count(FFI::addr($queryResult));

        $dataset = [];
        for ($row = 0; $row < $rowCount; $row++) {
            $rowData = [];
            for ($column = 0; $column < $columnCount; $column++) {
                $value = $duckDbFFI->duckdb_value_varchar(FFI::addr($queryResult), $column, $row);
                $rowData[$column] = ($value !== null ? FFI::string($value)  : '')." ";

                $duckDbFFI->duckdb_free($value);
            }

            $dataset[$row] = $rowData;
        }

        $duckDbFFI->duckdb_destroy_result(FFI::addr($queryResult));
        $duckDbFFI->duckdb_disconnect(FFI::addr($connection));
        $duckDbFFI->duckdb_close(FFI::addr($database));
        return $dataset;
    }

    public function parseDefinitions()
    {
        $this->duckDbFFI = \FFI::load(__DIR__.DIRECTORY_SEPARATOR.'duckdb-ffi.h');
    }
}
