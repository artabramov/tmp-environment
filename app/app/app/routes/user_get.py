from app import app
from app.core.app_response import app_response
from app.tasks.user_select import user_select


@app.route('/user/<user_id>', methods=['GET'])
def user_get(user_id):

    result = user_select.apply_async(args=[user_id])
    a = result.get()

    return app_response(*a)

