from flask import request, jsonify
from app import app, db
from app.models.user import User
from app.core.response import send_response
import werkzeug
#from app.workers import worker_user
from app.core import worker

@app.route('/user/', methods=['POST'])
def insert_user():

    result = worker.insert_user.apply_async(args=[
        request.args.get('user_email', None),
        request.args.get('user_password', None),
        request.args.get('user_name', None),
    ])
    a = result.get()

    return send_response(*a)


@app.route('/user/<user_id>', methods=['GET'])
def select_user(user_id):

    result = worker.select_user.apply_async(args=[user_id])
    a = result.get()

    return send_response(*a)

