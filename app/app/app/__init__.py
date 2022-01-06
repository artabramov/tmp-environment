from flask import Flask
from celery import Celery
from flask_sqlalchemy import SQLAlchemy
from .config import Config
from app.core.app_logger import app_logger


app = Flask(__name__)
app.config.from_object(Config)

app_logger(app)

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
from app.models import user
from app.models import user_meta
from app.models import user_token
from app.models import user_authcode

# routes
from app.routes import hello_world
from app.routes import user_post
from app.routes import user_get
from app.routes import create_tables

