FROM python:3.7
RUN apt-get update
RUN mkdir /app
RUN mkdir /var/log/app
RUN chown www-data /var/log/app
RUN mkdir /var/log/celery
RUN chown www-data /var/log/celery
WORKDIR /app

# apache
RUN apt-get install -y apache2 apache2-dev
COPY ./src/.htaccess ./
RUN a2enmod rewrite
RUN apt-get clean
COPY ./src/000-default.conf ./
RUN mv --force ./000-default.conf /etc/apache2/sites-available/000-default.conf

# venv & requirements
RUN python3.7 -m venv ./venv
RUN ./venv/bin/pip3.7 install mod_wsgi
RUN ln -s /app/venv/lib/python3.7/site-packages/mod_wsgi/server/mod_wsgi-*.so /app/venv/lib/python3.7/site-packages/mod_wsgi/server/mod_wsgi.so
COPY ./src/requirements.txt ./
RUN ./venv/bin/pip3.7 install --no-cache-dir -r ./requirements.txt
RUN ./venv/bin/pip3.7 install -U "celery[redis]"

# app
COPY ./app/ ./

EXPOSE 80
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
