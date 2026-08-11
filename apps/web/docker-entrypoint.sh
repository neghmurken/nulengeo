#!/bin/sh
set -e

if [ ! -d node_modules ]; then
	for attempt in 1 2 3 4 5; do
		yarn install && break
		echo "yarn install failed (attempt $attempt/5), retrying in 5s..." >&2
		sleep 5
	done
fi

exec "$@"
