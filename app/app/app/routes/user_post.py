from flask import request, jsonify
from app import app, db
from app.models.user import User
from app.core.response import send_response
import werkzeug

@app.route('/user/', methods=['POST'])
def user_post():
    #from app.tasks.post_msg import post_msg


    #result = post_msg.apply_async(args=[msg], ignore_result=True)

    #user_email=request.args.get('user_email', default=None, type=None)

    pending_user = User(
        user_status='pending',
        user_email=request.args.get('user_email', None),
        user_pass=request.args.get('user_pass', None),
        user_name=request.args.get('user_name', None),
    )

    #_user = db.select(User).where(user_email=pending_user.user_email).exists()
    #q = db.query(User).filter(User.user_email == pending_user.user_email)
    if User.query.filter_by(user_email=pending_user.user_email).first():
        raise werkzeug.exceptions.BadRequest('user_email already exists')

    try:
        db.session.add(pending_user)
        db.session.commit()
        #logger.info("success calling db func: " + func.__name__)
    except Exception as e:
        #logger.error(e.args)
        db.session.rollback()
        raise werkzeug.exceptions.BadRequest('user insert error')

    #return jsonify('post_msg...? result: ' + str(request))
    return send_response({'key': 'value'})
