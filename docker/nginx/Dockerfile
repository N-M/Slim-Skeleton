ARG NGINX_VERSION=1.21.1

FROM nginx:${NGINX_VERSION}-alpine

COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf

WORKDIR /srv/

COPY . /srv/
