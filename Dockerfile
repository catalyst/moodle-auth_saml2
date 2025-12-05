FROM moodlehq/moodle-php-apache:8.1
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /code
CMD ["composer", "update", "--no-dev"]