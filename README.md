# docker path
\\wsl$\docker-desktop-data\version-pack-data\community\docker\

# run celery
source /echidna/venv/bin/activate && celery -A app.workers.user_worker worker -n flask_worker.%n -Q user -f /var/log/celery/celery.log --loglevel=info

# run unittests
source /echidna/venv/bin/activate && cd /echidna && clear && python3 -m unittest -v app.tests.user_tests

# remove deattached celery
ps aux|grep 'celery worker'
sudo kill -9 process_id

# remove all volumes
docker volume rm $(docker volume ls -q)

# kill vmmem
wsl --shutdown