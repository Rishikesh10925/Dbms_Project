#!/bin/bash
set -e

# If Render (or other platform) provides a PORT env var, update Apache config to listen on it.
if [ -n "${PORT}" ]; then
  echo "[entrypoint] Setting Apache to listen on port ${PORT}"
  # Update ports.conf
  if [ -f /etc/apache2/ports.conf ]; then
    sed -ri "s/Listen [0-9]+/Listen ${PORT}/g" /etc/apache2/ports.conf || true
  fi
  # Update default site VirtualHost
  if [ -f /etc/apache2/sites-available/000-default.conf ]; then
    sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/g" /etc/apache2/sites-available/000-default.conf || true
  fi
fi

exec "$@"
