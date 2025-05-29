#!/usr/bin/bash
./vendor/bin/phpcbf src tests --standard=phpcs.xml
./vendor/bin/phpcbf src tests --standard=phpcs_psr12.xml

./vendor/bin/phpcs src tests --standard=phpcs.xml
./vendor/bin/phpcs src tests --standard=phpcs_psr12.xml

./vendor/bin/rector src templates config tests

./vendor/bin/phpstan analyse src --level=6 --memory-limit=1G

export XDEBUG_MODE=coverage && php bin/phpunit --coverage-html coverage
