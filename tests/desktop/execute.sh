#!/bin/bash

while [ -n "$1" ]
do
    find tests/$1 -name '*Cept.php' | while read test; do
        vendor/bin/codecept run --steps --debug --env env $1 $test || true;
    done
shift
done
vendor/bin/codecept run --steps --debug --env env -g failed || true;
