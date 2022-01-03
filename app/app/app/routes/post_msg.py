from flask import jsonify
from app import app, celery
from app.tasks.post_msg import post_msg

@app.route('/post_msg/<string:msg>')
def async_job(msg):

    result = post_msg.apply_async(args=[msg], ignore_result=False)
    a = result.get()

    return jsonify('task_id: ' + str(result) + ' row_id: ' + str(a))
