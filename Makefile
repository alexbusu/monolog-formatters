init:
	docker run --rm -v $$(pwd):/pkg --workdir=/pkg composer:2.6.5 install

composer-update:
	docker run --rm -v $$(pwd):/pkg --workdir=/pkg composer:2.6.5 update

phpunit:
	docker run --rm -v $$(pwd):/pkg --workdir=/pkg php:8.3.0-cli-alpine vendor/bin/phpunit

psalm:
	docker run --rm -v $$(pwd):/pkg --workdir=/pkg php:8.3.0-cli-alpine vendor/bin/psalm.phar --show-info=true
