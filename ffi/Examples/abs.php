<?php declare(strict_types=1);

$ffi = FFI::cdef(
    'int abs(int j);',
    'libc.so.6'
);

var_dump($ffi->abs(-42)); // int(42)
