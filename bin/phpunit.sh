#!/bin/bash

docker run --rm \
    -u $UID:$UID \
    -v $(pwd):/srv/o-data \
     docker.local:5000/o-data/php-cli:7.0.24 vendor/bin/phpunit $@
