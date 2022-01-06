from .. import celery
from ..models.user import User

@celery.task(name='app.user_select', ignore_result=False)
def user_select(user_id):

    user = User.query.filter_by(id=user_id).first()

    if not user:
        return 404, 'user rnot found', {}

    return 200, '', {'user': {
        'id': user.id,
        'user_email': user.user_email,
    }}
