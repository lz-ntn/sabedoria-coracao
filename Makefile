.PHONY: up down build logs ps shell migrate lint fmt test deploy

# ─── Desenvolvimento ────────────────────────────────────────

up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build

logs:
	docker compose logs -f

ps:
	docker compose ps

shell-%:
	docker compose exec $* sh

migrate-%:
	docker compose exec $* php database/migrate.php

# ─── Produção ───────────────────────────────────────────────

prod-up:
	docker compose -f docker-compose.prod.yml up -d

prod-down:
	docker compose -f docker-compose.prod.yml down

prod-build:
	docker compose -f docker-compose.prod.yml build

prod-logs:
	docker compose -f docker-compose.prod.yml logs -f

# ─── Qualidade ──────────────────────────────────────────────

lint:
	find api -name "*.php" -not -path "*/vendor/*" -exec php -l {} \; | grep -v "No syntax errors"

fmt:
	 which php-cs-fixer && php-cs-fixer fix api/ --rules=@PSR12 || echo "php-cs-fixer not installed"

test:
	 which phpunit && phpunit api/ || echo "PHPUnit not configured"

# ─── Deploy ─────────────────────────────────────────────────

deploy:
	git push origin main

deploy-force:
	git push origin main --force-with-lease

# ─── Utilitários ────────────────────────────────────────────

clean:
	docker compose down -v --remove-orphans

prune:
	docker system prune -f

help:
	@echo "Comandos disponíveis:"
	@echo "  up            - Inicia ambiente dev"
	@echo "  down          - Para ambiente dev"
	@echo "  build         - Constrói imagens dev"
	@echo "  logs          - Logs em tempo real"
	@echo "  shell-<svc>   - Shell no container (ex: make shell-portal-saberes)"
	@echo "  migrate-<svc> - Roda migrations (ex: make migrate-portal-saberes)"
	@echo "  prod-up       - Inicia ambiente produção"
	@echo "  lint          - Verifica sintaxe PHP"
	@echo "  fmt           - Formata código PHP"
	@echo "  test          - Roda testes"
	@echo "  deploy        - Push para GitHub"
	@echo "  clean         - Remove volumes e containers"
