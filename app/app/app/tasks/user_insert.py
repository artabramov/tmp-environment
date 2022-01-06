from .. import db, celery
from ..models.user import User


@celery.task(name='app.user_insert', ignore_result=False)
def user_insert(user_email, user_password, user_name):
    
    try:
        user = User(
            user_email=user_email,
            user_password=user_password,
            user_name=user_name,
        )
    except Exception as e:
        #logger.info("success calling db func: " + func.__name__)
        return e.code, e.description, {}

    if User.query.filter_by(user_email=user_email).first():
        #logger.info("success calling db func: " + func.__name__)
        return 400, 'user_email already exists', {}

    try:
        db.session.add(user)
        db.session.commit()
    except Exception as e:
        db.session.rollback()
        #logger.info("success calling db func: " + func.__name__)
        return 400, 'user insert error', {}

    return 200, '', {'user': {'id': user.id}}
