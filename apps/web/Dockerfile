#syntax=docker/dockerfile:1
FROM node:24-alpine

WORKDIR /app

RUN corepack enable

COPY --chmod=755 docker-entrypoint.sh /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["yarn", "dev", "--host"]
