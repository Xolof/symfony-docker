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

## About users

Anyone can register an account in the app, but only activated users can login.
Only a user with the role "ROLE_SUPER_ADMIN" can activate other users.

## How to become a "superadmin"
First, register through the web interface. Then, activate your account and assigning roles to it by running an SQL query.

Enter the MySQL container:
`./mysql.sh`

Update your account. replace <your_email> with the email address you used when you registered.
`UPDATE admin SET is_active=1, roles='["ROLE_USER", "ROLE_SUPER_ADMIN"]' WHERE email="<your_email>";`
