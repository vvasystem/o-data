#!/bin/bash

docker run --rm -t \
    -u $UID:$UID \
    -v $(pwd):/srv/o-data \
    -v $HOME/.cache/composer:$HOME/.composer \
    -v $HOME/.ssh:/home/$USER/.ssh \
    -v /etc/passwd:/etc/passwd:ro \
    -v /etc/group:/etc/group:ro \
    --network=host \
    docker.local:5000/o-data/php-cli:7.0.24 composer $@
