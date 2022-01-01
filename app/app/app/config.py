class Config:
    CELERY_BROKER_URL = 'amqp://guest:guest@host.docker.internal:5672//'
    CELERY_RESULT_BACKEND = 'rpc://'
    CELERY_TASK_LIST = ['app.tasks']
    SQLALCHEMY_DATABASE_URI = 'postgresql+psycopg2://postgres:postgres@host.docker.internal:5432/postgres' # dialect+driver://username:password@host:port/database
    SQLALCHEMY_TRACK_MODIFICATIONS = False
