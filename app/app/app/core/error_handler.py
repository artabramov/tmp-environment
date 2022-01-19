from app import app
from app.core.response_format import response_format


@app.errorhandler(Exception)
def error_handler(e):
    app.logger.critical(str(e))
    result = {
        'code': 500,
        'error': 'Internal Server Error',
        'data': {},
    }
    return response_format(result)
