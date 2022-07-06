PHONY :=
.DEFAULT_GOAL := help
SHELL := /bin/bash

OS := $(shell uname -s)

PHONY += up
up:			## Launch project
up:
	$(call colorecho, "\nStarting project on $(OS)")
	@docker-compose -f docker-compose.yml up -d
	@docker exec -it slim-php composer i

PHONY += down
down: 			## Tear down project
	$(call colorecho, "\nTear down project docker\n\n- Stopping all containers...\n")
	@docker-compose -f docker-compose.yml down --remove-orphans

PHONY += recreate
recreate: 			## Recreate docker containers
	$(call colorecho, "Recreate dev docker containers...\n")
	@docker-compose -f docker-compose.yml up -d --build --force-recreate --remove-orphans

##
##SSH (Docker)
##

PHONY += ssh-api
ssh-api:		## SSH to API container
ssh-api:
	$(call colorecho, "\nSSH to API container (slim-php docker image):\n")
	@docker exec -it slim-php /bin/sh

##
##Logs
##

PHONY += logs
logs:			## View Logs from Docker
logs:
	@docker-compose -f docker-compose.yml logs

define colorecho
	@tput -T xterm setaf 3
	@shopt -s xpg_echo && echo $1
	@tput -T xterm sgr0
endef


