from app import app
from app.core.app_response import app_response
from app.tasks.user_select import user_select


@app.route('/api/v1/user/<user_id>', methods=['GET'])
def user_get(user_id):

    try:
        result = user_select.apply_async(args=[user_id])
        data = result.get()
    except Exception as e:
        app.logger.critical(str(e))
        data = 500, 'internal server error', {}    

    return app_response(*data)

