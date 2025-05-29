#!/usr/bin/bash
./vendor/bin/phpcs src tests
./vendor/bin/phpcbf src tests
# ./vendor/bin/pint src templates config tests
./vendor/bin/rector src templates config tests
./vendor/bin/phpstan analyse src --memory-limit=1G

export XDEBUG_MODE=coverage && php bin/phpunit --coverage-html coverage

