from flask import request
from app import app
from app.core.response_format import response_format
from app.tasks.user_insert import user_insert

@app.route('/api/v1/user/', methods=['POST'])
def user_post():

    result = user_insert.apply_async(args=[
        request.args.get('user_email', None),
        request.args.get('user_password', None),
        request.args.get('user_name', None),
    ])
    data = result.get()

    return response_format(*data)

