# project2

# nginx
docker build -t nginx ./.nginx/
docker run -dit --name nginx -p 8081:80 nginx  
docker exec -it nginx bash
docker stop nginx && docker rm nginx && docker rmi nginx

# app
docker build -t app ./.app/  
docker run -dit --name app-1 -p 8082:80 app  
docker run -dit --name app-2 -p 8083:80 app  
docker exec -it app-1 bash
docker stop app-1 && docker rm app-1 && docker stop app-2 && docker rm app-2 && docker rmi app  



docker-compose stop app-1 && docker rm app-1 && docker rmi app && docker build -t app ./.app/ && docker-compose up -d

docker exec -it app-1 bash  
source ./venv/bin/activate  
celery -A app.tasks.post_msg worker -n post_worker.%n -Q post

docker exec -it rabbit-2 bash  
rabbitmqctl stop_app
rabbitmqctl join_cluster rabbit@rabbit-1
rabbitmqctl start_app

\\wsl$\docker-desktop-data\version-pack-data\community\docker\

** CELERY **  
source /app/venv/bin/activate
celery -A app.tasks.post_msg worker -n flask_worker.%n -Q post
celery -A app.workers.worker_user worker -n flask_worker.%n -Q user --detach
celery -A app.workers.worker_user control shutdown
celery -A app.core.app_worker worker -n flask_worker.%n -Q app -f /var/log/celery/celery.log --uid=www-data --loglevel=info
celery -A app.core.app_worker worker -n flask_worker.%n -Q app -f /var/log/celery/celery.log --loglevel=info


source /app/venv/bin/activate && celery -A app.core.app_worker worker -n flask_worker.%n -Q app -f /var/log/celery/celery.log --loglevel=info --detach