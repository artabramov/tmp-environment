FROM nginx:latest
#RUN ln -s /dev/stdout /var/log/nginx/access.log
#RUN ln -s /dev/stderr /var/log/nginx/error.log
RUN rm /var/log/nginx/access.log
RUN rm /var/log/nginx/error.log

COPY ./nginx.conf /etc/nginx/conf.d/default.conf

# app
#COPY ./app/ /usr/share/nginx/html

#\\wsl$\docker-desktop-data\version-pack-data\community\docker\volumes\project2_nginx_log\_data
