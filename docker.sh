#!/usr/bin/bash

email="$(git config --global user.email)"
name="$(git config --global user.name)"

docker compose exec -T php git config --global --add safe.directory /app

docker exec -it -e  XDEBUG_MODE=coverage \
    symfony-docker-php-1 bash -c \
    "echo \"alias st='git status'\" >> ~/.bashrc; source ~/.bashrc; \
     git config --global --replace-all user.email \"$email\"; \
     git config --global --replace-all user.name \"$name\"; \
     bash"
