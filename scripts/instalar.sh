#!/usr/bin/env bash
# Instala e valida tudo do zero: API (Docker), testes do back, do app e da IA.
set -euo pipefail
cd "$(dirname "$0")/.."
passo() { printf '\n\033[1;35m▶ %s\033[0m\n' "$*"; }
ok() { printf '\033[1;32m✔ %s\033[0m\n' "$*"; }

passo "1/5 Conferindo ferramentas"
for b in docker git node flutter php composer; do
  command -v "$b" >/dev/null || { echo "faltando: $b"; exit 1; }
  printf '  %-8s %s\n' "$b" "$(command -v "$b")"
done

passo "2/5 Configuração (.env com segredos aleatórios)"
if [ ! -f .env ]; then
  sed -e "s/^DB_PASSWORD=.*/DB_PASSWORD=$(openssl rand -hex 16)/" \
      -e "s/^JWT_SECRET=.*/JWT_SECRET=$(openssl rand -hex 32)/" .env.example > .env
  chmod 600 .env
  ok ".env criado (DB_PASSWORD e JWT_SECRET gerados)"
else
  ok ".env já existe — mantido"
fi

passo "3/5 Subindo Postgres + Redis + API PHP + Nginx (Docker)"
docker compose up -d --build
PORTA=$(grep -E '^API_PORTA=' .env | cut -d= -f2); PORTA=${PORTA:-8091}
for i in $(seq 1 60); do
  curl -fs "http://localhost:${PORTA}/saude" >/dev/null 2>&1 && break
  sleep 1
done
curl -fsS "http://localhost:${PORTA}/saude"; echo
ok "API no ar em http://localhost:${PORTA}"

passo "4/5 Back-end: composer install + PHPUnit"
(cd back && composer install --no-interaction --no-progress -q && vendor/bin/phpunit --no-progress)

passo "5/5 Mobile: flutter pub get + analyze + testes"
(cd mobile && flutter pub get >/dev/null && flutter analyze && flutter test --reporter compact)

ok "Instalação concluída"
