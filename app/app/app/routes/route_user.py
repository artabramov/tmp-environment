from flask import request, jsonify
from app import app, db
from app.models.user import User
from app.core.response import send_response
import werkzeug
from app.workers import worker_user

@app.route('/user/', methods=['POST'])
def insert_user():

    result = worker_user.insert_user.apply_async(args=[
        request.args.get('user_email', None),
        request.args.get('user_password', None),
        request.args.get('user_name', None),
    ])
 
    return send_response(*result.get())
