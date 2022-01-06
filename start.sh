#docker-compose stop
#docker rm app

docker build -t app ./app/
docker build -t app/nginx ./nginx/
docker-compose up -d
