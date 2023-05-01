<?php declare(strict_types=1);

$ffi = FFI::cdef(
    "struct tm {
        int tm_sec;
        int tm_min;
        int tm_hour;
        int tm_mday;
        int tm_mon;
        int tm_year;
        int tm_wday;
        int tm_yday;
        int tm_isdst;    
    };
    unsigned int mktime(struct tm * );",
    "libc.so.6"
);

$tm = $ffi->new("struct tm");
$tm->tm_mday = 1;   // days starts by 1
$tm->tm_mon = 0;    // months starts by 0 (January)
$tm->tm_year = 118; // years since 1900

var_dump($ffi->mktime(FFI::addr($tm)));  // int(1514761200)
