#  Cheat sheet

##  FFI

### Generate headers
Don't forget to comment out the following lines in duckdb.h:

```C
#include <stdbool.h>
#include <stdint.h>
#include <stdlib.h>
```

```bash
echo '#define FFI_LIB "./libduckdb.so"' >> duckdb-ffi.h
cpp -P -C -D"__attribute__(ARGS)=" duckdb.h >> duckdb-ffi.h
```

### Memory leaks detections
```bash
valgrind --leak-check=full php duckdb.php
```

### GDB
```bash
gdb --args php duckdb.php
```
Inside GDB:
Load php debug helpers:
```bash
source /data/php-src/.gdbinit
```
Dump backtrace:
```bash
(gdb) dump_bt executor_globals.current_execute_data
```

##  Extension

Skeleton generator:
```bash
php /data/php-src/ext/ext_skel.php --ext testExt --dir .
```

Compile extension:
```bash
phpize
./configure
make
make test
```
