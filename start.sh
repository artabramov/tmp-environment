docker build --no-cache -t echidna ./echidna/
docker-compose up -d

docker exec -d nginx rm /var/log/nginx/error.log
docker exec -d nginx rm /var/log/nginx/access.log
docker restart nginx

docker exec -d celery bash -c "source /echidna/venv/bin/activate && celery -A app.workers.user_worker worker -n flask_worker.%n -Q user -f /var/log/celery/celery.log --loglevel=info"

#docker exec -d mysql touch /var/log/mysql/mysql.log
#docker exec -d mysql chown mysql:mysql /var/log/mysql/mysql.log
#docker exec -d mysql mysql -u root -padmin -e "SET GLOBAL general_log='ON';"
#docker exec -d mysql mysql -u root -padmin -e "SET GLOBAL general_log_file='/var/log/mysql/mysql.log';"
#docker exec -d mysql touch /var/log/mysql/mysql-slow.log
#docker exec -d mysql chown mysql:mysql /var/log/mysql/mysql-slow.log
#docker exec -d mysql mysql -u root -padmin -e "SET GLOBAL slow_query_log=1;"
#docker exec -d mysql mysql -u root -padmin -e "SET GLOBAL slow_query_log_file='/var/log/mysql/mysql-slow.log';"
#docker exec -d mysql mysql -u root -padmin -e "SET GLOBAL long_query_time=1;"
#docker exec -d mysql mysql -u root -padmin -e "SET GLOBAL log_queries_not_using_indexes=0;"
