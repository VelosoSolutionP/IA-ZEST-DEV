# IA-ZEST-DEV — app de clientes (PHP + Flutter)

API REST em **PHP 8.4** (JWT, bcrypt, Postgres, Redis, Docker) e app **Flutter** com login e cadastro de clientes.

```
back/     API REST (Slim 4) em camadas, migrações e seeds (Phinx), PHPUnit
mobile/   App Flutter (core + features/{domain,data,presentation}), testes
```

## Rodar

```bash
scripts/instalar.sh      # cria .env, sobe Postgres + Redis + API (Docker) e roda os testes
```

API em `http://localhost:8091` · usuário de demonstração `demo@iazest.dev` / `Demo@2026`.
APK: `scripts/build-apk.sh http://IP-DA-MAQUINA:8091`.

## Histórico deste repositório

- `main`: projeto + um commit que introduziu um bug (telefone celular com 11 dígitos recusado).
- `fix/fabiano.veloso/celular-com-11-digitos-recusado`: correção feita pela IA Dev Mobile da Veloso Solution,
  com branch e commit no formato combinado e testes verdes antes do commit.
