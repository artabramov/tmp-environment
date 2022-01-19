from app import app, db
from flask import request
from app.core.error_handler import error_handler
from app.core.response_format import response_format

@app.route('/api/v1/migrate')
def migrate():
    db.create_all()
    #r = request
    result = {
        'code': 200, 
        'error': '',
        'data': {},
    }   

    return response_format(result)
