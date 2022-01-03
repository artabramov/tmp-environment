class Config:
    CELERY_BROKER_URL = 'amqp://guest:guest@host.docker.internal:5672//'
    CELERY_RESULT_BACKEND = 'redis://host.docker.internal:6379/0'
    CELERY_TASK_LIST = ['app.tasks', 'app.workers']
    CELERY_RESULT_EXPIRES = 60
    CELERY_TASK_ROUTES = {
        'post.*': {'queue': 'post'},
        'user.*': {'queue': 'user'}
    }

    SQLALCHEMY_DATABASE_URI = 'postgresql+psycopg2://postgres:postgres@host.docker.internal:5432/postgres' # dialect+driver://username:password@host:port/database
    SQLALCHEMY_TRACK_MODIFICATIONS = False

    USER_PASSWORD_SALT = 'paSS-$alt!'
    USER_AUTHCODE_SALT = 'aUTH-$alt?'