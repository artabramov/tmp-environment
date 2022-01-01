from flask import Flask, jsonify, make_response
from celery import Celery
from flask_sqlalchemy import SQLAlchemy
from .config import Config
import werkzeug

app = Flask(__name__)
app.config.from_object(Config)

db = SQLAlchemy(app)

def make_celery():
    celery = Celery(
        broker=app.config['CELERY_BROKER_URL'],
        backend=app.config['CELERY_RESULT_BACKEND'],
        include=app.config['CELERY_TASK_LIST'],
    )

    celery.conf.task_routes = {
        'post.*': {'queue': 'post'}
    }

    TaskBase = celery.Task

    class ContextTask(TaskBase):
        abstract = True

        def __call__(self, *args, **kwargs):
            with app.app_context():
                return TaskBase.__call__(self, *args, **kwargs)

    celery.Task = ContextTask
    return celery


#from app.models import user_token
from app.models import msg
from app.models import user_token
from app.models import user
from app.models import user_meta

# routes
from app.routes import user_post
from app.routes import hello_world
from app.routes import create_tables
from app.routes import post_msg


@app.errorhandler(werkzeug.exceptions.BadRequest)
@app.errorhandler(werkzeug.exceptions.Unauthorized)
def handle_bad_request(e):

    #return 'bad request!', 400
    #return app.response_class(
    #    #response=json.dumps(data),
    #    response=jsonify('post_msg...? result: '),
    #    status=200,
    #    mimetype='application/json'
    #)

    response = make_response(
        jsonify(
            {
                'error': e.description, 
                'success': 'false'
            }
        ),
        e.code,
    )
    response.headers['Content-Type'] = 'application/json'
    response.headers['Access-Control-Allow-Origin'] = '*'
    response.headers['Server'] = 'noserver'
    return response

"""
@app.request_started
def send_response(data={}):
    response = make_response(
        jsonify(
            {
                'error': '', 
                'success': 'true',
                'data': data
            }
        ),
        200,
    )
    response.headers['Content-Type'] = 'application/json'
    response.headers['Access-Control-Allow-Origin'] = '*'
    response.headers['Server'] = 'noserver'
    return response
"""
