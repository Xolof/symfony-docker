# Symfony Docker / Badger Website

[![CI](https://github.com/Xolof/symfony-docker/actions/workflows/ci.yml/badge.svg)](https://github.com/Xolof/symfony-docker/actions/workflows/ci.yml)

An application for experimenting with Symfony.

[Symfony Docker](https://github.com/dunglas/symfony-docker) has been used as a starting point.

## Start the project

`docker compose up`

`composer install`

`bin/console doctrine:migrations:migrate`

## Tests

The script `tests.sh` has been configured to run before a git commit.
