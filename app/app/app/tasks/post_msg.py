from .. import make_celery
from .. import db
from ..models import msg
import time

celery = make_celery()

@celery.task(name='post.post_msg')
def post_msg(tmp):
    time.sleep(5)
    obj = msg.Msg(msg=tmp)
    db.session.add(obj)
    db.session.commit()
    return obj.id
