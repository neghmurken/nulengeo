#syntax=docker/dockerfile:1
FROM dunglas/frankenphp:1-php8.4

WORKDIR /app

RUN install-php-extensions \
	@composer \
	apcu \
	intl \
	opcache \
	zip

# Writable by any container UID: the api service runs as the host user (see compose.yaml),
# not the root this image's Caddy state dirs are created for.
RUN chmod -R 777 /data/caddy /config/caddy

COPY frankenphp/Caddyfile /etc/frankenphp/Caddyfile
COPY --chmod=755 frankenphp/docker-entrypoint.sh /usr/local/bin/docker-entrypoint

ENV APP_ENV=dev

ENTRYPOINT ["docker-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
