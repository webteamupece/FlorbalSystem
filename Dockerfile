FROM php:latest

WORKDIR /var/www/html

COPY src/index.php .

EXPOSE 80

CMD [ "php", "-S", "0.0.0.0:80", "index.php" ]
