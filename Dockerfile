FROM php:apache

WORKDIR /
COPY . /var/www/html
RUN a2enmod actions alias allowmethods asis authz_groupfile cgi include info mime negotiation rewrite session_cookie setenvif socache_shmcb ssl status && \
    echo "ServerName localhost:80" >> /etc/apache2/apache2.conf && \
    service apache2 restart

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
