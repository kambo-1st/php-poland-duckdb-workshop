<?php

// TODO start coding here...

/*
#include <stdio.h>
#include <stdint.h>
#include <stdbool.h>
#include <stdlib.h>
#include "duckdb.h"

int main() {
    // SLIDE explain data types
    duckdb_database db;
    duckdb_connection con;

    // SLIDE explain pointers + enums
    if (duckdb_open(NULL, &db) == DuckDBError) {
        duckdb_disconnect(&con);
        duckdb_close(&db);
        exit(1);
    }

    if (duckdb_connect(db, &con) == DuckDBError) {
        duckdb_disconnect(&con);
        duckdb_close(&db);
        exit(1);
    }

    // run queries...
    duckdb_state state;
    duckdb_result result;

    // create a table
    state = duckdb_query(con, "CREATE TABLE integers(i INTEGER, j INTEGER);", NULL);
    if (state == DuckDBError) {
        duckdb_destroy_result(&result);
        duckdb_disconnect(&con);
        duckdb_close(&db);
        exit(1);
    }

    // insert three rows into the table
    state = duckdb_query(con, "INSERT INTO integers VALUES (3, 4), (5, 6), (7, NULL);", NULL);
    if (state == DuckDBError) {
        duckdb_destroy_result(&result);
        duckdb_disconnect(&con);
        duckdb_close(&db);
        exit(1);
    }

    // query rows again
    state = duckdb_query(con, "SELECT * FROM integers", &result);
    if (state == DuckDBError) {
        printf("%s", duckdb_result_error(&result));
        duckdb_destroy_result(&result);
        duckdb_disconnect(&con);
        duckdb_close(&db);
        exit(1);
    }

    // SLIDE explain structures
    printf("Number of columns: %ld", result->__deprecated_column_count);

	idx_t row_count = duckdb_row_count(&result);
	idx_t column_count = duckdb_column_count(&result);

	// print the data of the result
	for (size_t row_idx = 0; row_idx < row_count; row_idx++) {
		for (size_t col_idx = 0; col_idx < column_count; col_idx++) {
			char *val = duckdb_value_varchar(&result, col_idx, row_idx);
			printf("%s ", val);
            // SLIDE - memory management PHP vs C
			duckdb_free(val);
		}
		printf("\n");
	}

    // destroy the result after we are done with it
    duckdb_destroy_result(&result);

    // cleanup
    duckdb_disconnect(&con);
    duckdb_close(&db);
}

*/

/*
Generate headers
Don't forget to comment out the following lines in duckdb.h:

#include <stdbool.h>
#include <stdint.h>
#include <stdlib.h>

echo '#define FFI_LIB "./libduckdb.so"' >> duckdb-ffi.h
cpp -P -C -D"__attribute__(ARGS)=" duckdb.h >> duckdb-ffi.h

Memory leaks detections
valgrind --leak-check=full php duckdb.php

// GDB
gdb --args php duckdb.php
source /data/php-src/.gdbinit
(gdb) dump_bt executor_globals.current_execute_data
*/
