from app import app
from app.core.response_format import response_format
from app.tasks.user_select import user_select


@app.route('/api/v1/user/<user_id>', methods=['GET'])
def user_get(user_id):

    try:
        result = user_select.apply_async(args=[user_id])
        data = result.get()
    except Exception as e:
        app.logger.critical(str(e))
        data = 500, 'internal server error', {}    

    return response_format(*data)

