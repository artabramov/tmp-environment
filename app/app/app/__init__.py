from flask import Flask
from celery import Celery
from flask_sqlalchemy import SQLAlchemy
from .config import Config
from app.core.logger_wrapper import LoggerWrapper
import os, pwd, grp


app = Flask(__name__)
app.config.from_object(Config)

if not os.path.isfile(app.config['LOG_FILENAME']):
    open(app.config['LOG_FILENAME'], 'a').close()
    uid = pwd.getpwnam('www-data').pw_uid
    gid = grp.getgrnam('root').gr_gid
    os.chown(app.config['LOG_FILENAME'], uid, gid)

log = LoggerWrapper(app)
db = SQLAlchemy(app)

"""
def make_celery():
    celery = Celery(
        broker=app.config['CELERY_BROKER_URL'],
        backend=app.config['CELERY_RESULT_BACKEND'],
        include=app.config['CELERY_TASK_LIST'],
    )

    celery.conf.task_routes = app.config['CELERY_TASK_ROUTES']
    celery.conf.result_expires = app.config['CELERY_RESULT_EXPIRES']

    TaskBase = celery.Task

    class ContextTask(TaskBase):
        abstract = True

        def __call__(self, *args, **kwargs):
            with app.app_context():
                return TaskBase.__call__(self, *args, **kwargs)

    celery.Task = ContextTask
    return celery
celery = make_celery()
"""

celery = Celery(
    broker=app.config['CELERY_BROKER_URL'],
    backend=app.config['CELERY_RESULT_BACKEND'],
    include=app.config['CELERY_TASK_LIST'],
)
celery.conf.task_routes = app.config['CELERY_TASK_ROUTES']
celery.conf.result_expires = app.config['CELERY_RESULT_EXPIRES']

# models
#from app.models import user
#from app.models import user_meta
#from app.models import user_token

# routes
from app.routes import hello
#from app.routes import migrate
from app.routes import user_routes


