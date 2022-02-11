<?php

if (!is_file('vendor/autoload.php')) {
    echo "Run `make install` first.\n";
    exit(1);
}
require_once 'vendor/autoload.php';
