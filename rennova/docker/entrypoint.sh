#!/bin/bash
set -e

# Iniciar Apache en segundo plano
apache2-foreground &

# Iniciar cron
service cron start

# Esperar indefinidamente
wait
