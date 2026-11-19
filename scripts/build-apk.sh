#!/usr/bin/env bash
# Gera o APK de release assinado.  Uso: scripts/build-apk.sh [API_URL]
#   API_URL padrão: http://10.0.2.2:8091 (emulador Android -> API na máquina)
#   Celular na mesma rede: scripts/build-apk.sh http://IP-DA-MAQUINA:8091
#   Produção: scripts/build-apk.sh https://api.seudominio.com.br   (use HTTPS)
set -euo pipefail
cd "$(dirname "$0")/../mobile"
API_URL=${1:-http://10.0.2.2:8091}
VERSAO=$(grep -E '^version:' pubspec.yaml | awk '{print $2}' | cut -d+ -f1)
EXTRA=()
[[ "$API_URL" == https://* ]] && EXTRA+=(--android-project-arg=soHttps=true)
[ -f android/key.properties ] && echo "assinatura: chave de release (android/key.properties)" || echo "AVISO: sem android/key.properties — assinando com chave de debug"

flutter build apk --release --dart-define=API_URL="$API_URL" "${EXTRA[@]}"

mkdir -p ../dist
SAIDA="../dist/ia-zest-v${VERSAO}.apk"
cp build/app/outputs/flutter-apk/app-release.apk "$SAIDA"
echo
echo "APK: $(cd ../dist && pwd)/$(basename "$SAIDA")"
du -h "$SAIDA" | cut -f1 | sed 's/^/tamanho: /'
sha256sum "$SAIDA" | cut -d' ' -f1 | sed 's/^/sha256:  /'
echo "API_URL embutida: $API_URL"
