#!/bin/bash

while [ -n "$1" ]
do
    find tests/$1 -name '*Cept.php' | while read test; do
        vendor/bin/codecept run --steps --debug --env browserstack $1 $test || true;
    done
shift
done