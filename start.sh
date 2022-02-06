docker build -t echidna ./echidna/
#docker build -t app/nginx ./nginx/
docker-compose up -d

docker exec -d nginx rm /var/log/nginx/error.log
docker exec -d nginx rm /var/log/nginx/access.log
docker restart nginx

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
