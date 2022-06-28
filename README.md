# demo-inspector

This project is without private frames from closed repositories, but to check the problem in the browserstack is more than enough

Before starting, you must install the composer and run "composer install", PHP version 7.4

You only need to check the launch of the tests on browserstack using your CI\CD and not how they pass.

command to run tests from pipeline, not from local machine!!! "vendor/bin/robo parallel::all || true"