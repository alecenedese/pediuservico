FROM php:8.4-apache

# Extensões usadas pelo app (mysqli no fluxo principal; pdo_mysql para utilitários)
RUN docker-php-ext-install mysqli pdo_mysql \
    && a2enmod rewrite

# Código da aplicação
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

# Entrypoint: gera o send.php a partir das variáveis de ambiente (sem segredo no repo)
# e ajusta o Apache para a porta dinâmica do Render ($PORT).
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
