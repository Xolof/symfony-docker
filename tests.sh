#!/usr/bin/bash
./vendor/bin/pint src templates config tests
./vendor/bin/rector src templates config tests
./vendor/bin/phpstan analyse src --memory-limit=1G
