DOCKER_COMPOSE?=docker-compose
RUN=$(DOCKER_COMPOSE) run --rm app
EXEC?=$(DOCKER_COMPOSE) exec app
COMPOSER=$(EXEC) composer
CONSOLE=bin/console
PHPCSFIXER?=$(EXEC) php -d memory_limit=1024m vendor/bin/php-cs-fixer
BEHAT_ARGS?=-vvv

.DEFAULT_GOAL := help
.PHONY: help start stop reset db db-diff db-diff-dump db-migrate db-rollback db-load watch clear clean test tu tf tj lint ls ly lt
.PHONY: lj build up perm deps cc phpcs phpcsfix tty tfp tfp-rabbitmq tfp-db test-behat test-phpunit-functional
.PHONY: wait-for-rabbitmq wait-for-db security-check

help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

##
## Project setup
##---------------------------------------------------------------------------

start: build up app/config/parameters.yml db rabbitmq-fabric web/built assets-amp var/public.key perm  ## Install and start the project

stop:                                                                                                  ## Remove docker containers
	$(DOCKER_COMPOSE) kill
	$(DOCKER_COMPOSE) rm -v --force

reset: stop start

clear: perm                                                                                            ## Remove all the cache, the logs, the sessions and the built assets
	-$(EXEC) rm -rf var/cache/*
	-$(EXEC) rm -rf var/sessions/*
	-$(EXEC) rm -rf supervisord.log supervisord.pid npm-debug.log .tmp
	-$(EXEC) $(CONSOLE) redis:flushall -n
	rm -rf var/logs/*
	rm -rf web/built
	rm var/.php_cs.cache

clean: clear                                                                                           ## Clear and remove dependencies
	rm -rf vendor node_modules

cc:                                                                                                    ## Clear the cache in dev env
	$(EXEC) $(CONSOLE) cache:clear --no-warmup
	$(EXEC) $(CONSOLE) cache:warmup

tty:                                                                                                   ## Run app container in interactive mode
	$(RUN) /bin/bash

var/public.key: var/private.key                                                                        ## Generate the public key
	$(EXEC) openssl rsa -in var/private.key -pubout -out var/public.key

var/private.key:                                                                                       ## Generate the private key
	$(EXEC) openssl genrsa -out var/private.key 1024

wait-for-rabbitmq:
	$(EXEC) php -r "set_time_limit(60);for(;;){if(@fsockopen('rabbitmq',5672)){break;}echo \"Waiting for RabbitMQ\n\";sleep(1);}"

rabbitmq-fabric: wait-for-rabbitmq
	$(EXEC) $(CONSOLE) rabbitmq:setup-fabric

##
## Database
##---------------------------------------------------------------------------

wait-for-db:
	$(EXEC) php -r "set_time_limit(60);for(;;){if(@fsockopen('db',3306)){break;}echo \"Waiting for MySQL\n\";sleep(1);}"

db: vendor wait-for-db                                                                                 ## Reset the database and load fixtures
	$(EXEC) $(CONSOLE) doctrine:database:drop --force --if-exists
	$(EXEC) $(CONSOLE) doctrine:database:create --if-not-exists
	$(EXEC) $(CONSOLE) doctrine:database:import -n -- dump/dump-2017.sql
	$(EXEC) $(CONSOLE) doctrine:migrations:migrate -n
	$(EXEC) $(CONSOLE) doctrine:fixtures:load -n

db-diff: vendor wait-for-db                                                                            ## Generate a migration by comparing your current database to your mapping information
	$(EXEC) $(CONSOLE) doctrine:migration:diff

db-diff-dump: vendor wait-for-db                                                                       ## Generate a migration by comparing your current database to your mapping information and display it in console
	$(EXEC) $(CONSOLE) doctrine:schema:update --dump-sql

db-migrate: vendor wait-for-db                                                                         ## Migrate database schema to the latest available version
	$(EXEC) $(CONSOLE) doctrine:migration:migrate -n

db-rollback: vendor wait-for-db                                                                        ## Rollback the latest executed migration
	$(EXEC) $(CONSOLE) doctrine:migration:migrate prev -n

db-load: vendor wait-for-db                                                                            ## Reset the database fixtures
	$(EXEC) $(CONSOLE) doctrine:fixtures:load -n

db-validate: vendor wait-for-db                                                                        ## Check the ORM mapping
	$(EXEC) $(CONSOLE) doctrine:schema:validate


##
## Assets
##---------------------------------------------------------------------------

watch: node_modules                                                                                    ## Watch the assets and build their development version on change
	$(EXEC) yarn watch

assets: node_modules                                                                                   ## Build the development version of the assets
	$(EXEC) yarn build-dev

assets-prod: node_modules                                                                              ## Build the production version of the assets
	$(EXEC) yarn build-prod

assets-amp: node_modules                                                                               ## Build the production version of the AMP CSS
	$(EXEC) yarn build-amp


##
## Tests
##---------------------------------------------------------------------------

test: tu tf tj                                                                                         ## Run the PHP and the Javascript tests

test-behat:                                                                                            ## Run behat tests
	$(EXEC) vendor/bin/behat $(BEHAT_ARGS)

test-phpunit-functional:                                                                               ## Run phpunit fonctional tests
	$(EXEC) vendor/bin/phpunit --group functional

tu: vendor app/config/assets_version.yml                                                               ## Run the PHP unit tests
	$(EXEC) vendor/bin/phpunit --exclude-group functional

tf: tfp test-behat test-phpunit-functional                                                             ## Run the PHP functional tests

tfp: assets-amp assets-prod vendor perm tfp-rabbitmq tfp-db                                            ## Prepare the PHP functional tests

tfp-rabbitmq: wait-for-rabbitmq                                                                        ## Init RabbitMQ setup for tests
	$(DOCKER_COMPOSE) exec rabbitmq rabbitmqctl add_vhost /test || true
	$(DOCKER_COMPOSE) exec rabbitmq rabbitmqctl set_permissions -p /test guest ".*" ".*" ".*"
	$(EXEC) $(CONSOLE) --env=test rabbitmq:setup-fabric

tfp-db: wait-for-db                                                                                    ## Init databases for tests
	$(EXEC) rm -rf /tmp/data.db app/data/dumped_referents_users || true
	$(EXEC) $(CONSOLE) doctrine:database:create --env=test_sqlite
	$(EXEC) $(CONSOLE) doctrine:schema:create --env=test_sqlite
	$(EXEC) $(CONSOLE) doctrine:database:drop --force --if-exists --env=test_mysql
	$(EXEC) $(CONSOLE) doctrine:database:create --env=test_mysql
	$(EXEC) $(CONSOLE) doctrine:database:import --env=test_mysql -n -- dump/dump-2017.sql
	$(EXEC) $(CONSOLE) doctrine:migration:migrate -n --env=test_mysql
	$(EXEC) $(CONSOLE) doctrine:schema:validate --env=test_mysql

tj: node_modules                                                                                       ## Run the Javascript tests
	$(EXEC) yarn test

lint: ls ly lt lj phpcs                                                                                ## Run lint on Twig, YAML, PHP and Javascript files

ls: ly lt                                                                                              ## Lint Symfony (Twig and YAML) files

ly:
	$(EXEC) $(CONSOLE) lint:yaml app/config

lt:
	$(EXEC) $(CONSOLE) lint:twig templates

lj: node_modules                                                                                       ## Lint the Javascript to follow the convention
	$(EXEC) yarn lint

ljfix: node_modules                                                                                    ## Lint and try to fix the Javascript to follow the convention
	$(EXEC) yarn lint -- --fix

phpcs: vendor                                                                                          ## Lint PHP code
	$(PHPCSFIXER) fix --diff --dry-run --no-interaction -v

phpcsfix: vendor                                                                                       ## Lint and fix PHP code to follow the convention
	$(PHPCSFIXER) fix

security-check: vendor                                                                                 ## Check for vulnerable dependencies
	$(EXEC) vendor/bin/security-checker security:check


##
## Dependencies
##---------------------------------------------------------------------------

deps: vendor web/built                                                                                 ## Install the project PHP and JS dependencies

##


# Internal rules

build:
	$(DOCKER_COMPOSE) pull --parallel --ignore-pull-failures
	$(DOCKER_COMPOSE) build --force-rm

up:
	$(DOCKER_COMPOSE) up -d --remove-orphans

perm:
	$(EXEC) chmod -R 777 var app/data/images
	$(EXEC) chown -R www-data:root var
	$(EXEC) chmod 660 var/public.key var/private.key

# Rules from files

vendor: composer.lock
	$(COMPOSER) install -n

composer.lock: composer.json
	@echo compose.lock is not up to date.

app/config/parameters.yml: app/config/parameters.yml.dist vendor
	$(EXEC) composer -n run-script post-install-cmd

node_modules: yarn.lock
	$(EXEC) yarn install

yarn.lock: package.json
	@echo yarn.lock is not up to date.

web/built: front node_modules
	$(EXEC) yarn build-dev

app/config/assets_version.yml:
	 $(EXEC) yarn build-prod
