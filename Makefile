ifndef PHP
	PHP=7.2
endif
ifndef PHPUNIT
	PHPUNIT=7.5
endif

dockerized=docker run --init -it --rm \
	-u $(shell id -u):$(shell id -g) \
	-v $(shell pwd):/app \
	-w /app
qa=${dockerized} \
	-e COMPOSER_CACHE_DIR=/app/var/composer \
	-e SYMFONY_PHPUNIT_DIR=/app/var/phpunit \
	-e SYMFONY_PHPUNIT_VERSION=${PHPUNIT} \
	jakzal/phpqa:php${PHP}-alpine
mkdocs=${dockerized} -p 8000:8000 squidfunk/mkdocs-material
composer_args=--prefer-dist --no-progress --no-interaction --no-suggest

# deps
install: phpunit-install
	${qa} composer install ${composer_args}
update: phpunit-install
	${qa} composer update ${composer_args}
install-standalone:
	${qa} bin/package-exec composer install ${composer_args}
update-standalone:
	${qa} bin/package-exec composer update ${composer_args}
update-standalone-lowest:
	${qa} bin/package-exec composer update ${composer_args} --prefer-stable --prefer-lowest

# tests
phpunit-install:
	${qa} bin/package-exec simple-phpunit install
phpunit:
	${qa} bin/package-exec simple-phpunit
phpunit-coverage:
	${qa} bin/package-exec phpdbg -qrr /tools/simple-phpunit --coverage-clover=coverage.xml

# code style / static analysis
cs:
	${qa} php-cs-fixer fix --dry-run --verbose --diff --config=.php_cs src/ tests/
cs-fix:
	${qa} php-cs-fixer fix --config=.php_cs src/ tests/
sa: install
	mkdir -p $$(find src/ -mindepth 1 -maxdepth 1 -type d -print -quit)/vendor
	#${qa} phpstan analyse # https://github.com/jakzal/phpqa/issues/139
	${qa} psalm --show-info=false

# docs
docs-serve:
	${mkdocs}
docs-build:
	${mkdocs} build --site-dir var/build/docs

# linting
lint-yaml:
	${dockerized} sdesbure/yamllint yamllint .yamllint .*.yml *.yml

# CI
ci-install:
	${qa} bin/package-exec composer require --no-update --quiet symfony/debug:^4.2.2
	${qa} bin/ci-packager HEAD^ $$(find src/*/composer.json -type f -printf '%h\n')

# misc
clean:
	rm -rf var/phpstan var/psalm var/php-cs-fixer.cache
smoke-test: clean update update-standalone phpunit cs sa
shell:
	${qa} /bin/sh
link: install install-standalone
	${qa} bin/package-exec composer link --working-dir=/app "\$$(pwd)"
test-project:
	${qa} composer create-project --prefer-dist --no-progress --no-interaction symfony/skeleton var/test-project
	${qa} composer config --working-dir=var/test-project extra.symfony.allow-contrib true
	${qa} composer config --working-dir=var/test-project repositories.msgphp path "../../src/*"
	${qa} composer require --no-update --working-dir=var/test-project ${composer_args} orm
	${qa} composer require --working-dir=var/test-project --dev ${composer_args} debug maker server
composer-normalize: install install-standalone
	${qa} composer normalize
	${qa} bin/package-exec composer normalize
