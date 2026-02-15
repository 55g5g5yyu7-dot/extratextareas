#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
RUNTIME_DIR="${ROOT_DIR}/.modx-runtime"
DIST_DIR="${ROOT_DIR}/dist"
PACKAGE_GLOB='extratextareas-*.transport.zip'

rm -rf "${RUNTIME_DIR}"
mkdir -p "${RUNTIME_DIR}" "${DIST_DIR}"

pushd "${RUNTIME_DIR}" >/dev/null
composer create-project modx/revolution modx --no-interaction --quiet
cd modx

mysql -h 127.0.0.1 -u"${MYSQL_USER}" -p"${MYSQL_PASSWORD}" -e "CREATE DATABASE IF NOT EXISTS ${MYSQL_DATABASE} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

php setup/cli-install.php \
  --mode=new \
  --database_server=127.0.0.1 \
  --database="${MYSQL_DATABASE}" \
  --database_user="${MYSQL_USER}" \
  --database_password="${MYSQL_PASSWORD}" \
  --table_prefix=modx_ \
  --language=en \
  --cmsadmin=admin \
  --cmspassword=admin1234 \
  --cmsadminemail=admin@example.com \
  --context_web_url=/ \
  --context_mgr_url=/manager/ \
  --context_connectors_url=/connectors/ \
  --remove_setup_directory=0
popd >/dev/null

MODX_BASE_PATH="${RUNTIME_DIR}/modx" php "${ROOT_DIR}/_build/build.transport.php"

cp "${RUNTIME_DIR}/modx/core/packages"/${PACKAGE_GLOB} "${DIST_DIR}/"
LATEST_PACKAGE="$(ls -1t "${DIST_DIR}"/${PACKAGE_GLOB} | head -n 1)"
cp "${LATEST_PACKAGE}" "${DIST_DIR}/extratextareas-latest.transport.zip"

echo "Built: ${LATEST_PACKAGE}"
