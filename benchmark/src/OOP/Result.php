<?php declare(strict_types=1);

namespace Kambo\DuckDB\OOP;

/**
 *
 */
class Result
{
    private $queryResult;

    public function __construct() {
        $duckDBFFI = DuckDBFFI::getInstance();
        $this->queryResult = $duckDBFFI->new('duckdb_result');
    }

    public function toInternalDataStructure() {
        return $this->queryResult;
    }

    public function toArray() : array {
        $duckDBFFI   = DuckDBFFI::getInstance();
        $queryResult = $this->queryResult;

        $rowCount = $duckDBFFI->duckdb_row_count(\FFI::addr($queryResult));
        $columnCount = $duckDBFFI->duckdb_column_count(\FFI::addr($queryResult));
        $dataset = [];
        for ($row = 0; $row < $rowCount; $row++) {
            $rowData = [];
            for ($column = 0; $column < $columnCount; $column++) {
                $value = $duckDBFFI->duckdb_value_varchar(\FFI::addr($queryResult), $column, $row);
                $rowData[$column] = ($value !== null ? \FFI::string($value)  : '')." ";
                $duckDBFFI->duckdb_free($value);
            }

            $dataset[$row] = $rowData;
        }

        return $dataset;
    }

    public function __destruct() {
        $duckDBFFI = DuckDBFFI::getInstance();
        $duckDBFFI->duckdb_destroy_result(\FFI::addr($this->queryResult));
    }
}
