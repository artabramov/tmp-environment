from flask import jsonify
from app import app, db

@app.route('/api/v1/create_tables')
def create_tales():
    db.create_all()
    return jsonify('create tables?...')
