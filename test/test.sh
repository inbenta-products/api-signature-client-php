#!/bin/bash
cd "$(dirname "$0")"
./../vendor/bin/phpunit "${@:1}" -v
