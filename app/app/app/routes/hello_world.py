from flask import jsonify
from app import app

@app.route('/')
def hello_world():
    app.logger.error('any error')
    app.logger.critical('critical error')
    return jsonify('Hello, world!')
