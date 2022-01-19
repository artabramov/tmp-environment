from .. import app, db, celery
from ..models.user import User
from ..models.user_token import UserToken
import werkzeug

@celery.task(name='app.user_insert', time_limit=10, ignore_result=False)
def user_insert(user_email, user_password, user_name, remote_addr, user_agent):

    app.logger.critical('fuck critical!')
    app.logger.debug('fuck debug!')
    app.logger.error('fuck error!')
    
    try:
        user = User(
            user_email=user_email,
            user_password=user_password,
            user_name=user_name,
        )
    except werkzeug.exceptions.BadRequest as e:
        return {
            'code': e.code, 
            'error': e.description, 
            'data': {},
        }
    except Exception as e:
        return {
            'code': 400, 
            'error': 'user insert error',
            'data': {},
        }


    try:
        user_token = UserToken(user.id, remote_addr, user_agent)
    except Exception as e:
        return {
            'code': 400, 
            'error': 'user_token insert error', 
            'data': {},
        }


    try:
        db.session.add(user)
        db.session.add(user_token)
        db.session.commit()
    except werkzeug.exceptions.BadRequest as e:
        db.session.rollback()
        return {
            'code': e.code, 
            'error': e.description, 
            'data': {},
        }
    except Exception as e:
        db.session.rollback()
        app.logger.critical('fuck 2!')
        print('fuck!')
        app.logger.critical(str(e))
        return {
            'code': 400, 
            'error': 'user insert error 2!', 
            'data': {},
        }

    return {
        'code': 200, 
        'error': '', 
        'data': {'user': {'id': user.id}},
    }
