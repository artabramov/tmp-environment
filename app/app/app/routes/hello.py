from app import app, log
#from app.core.error_handler import error_handler
from app.core.response_format import response_format

@app.route('/api/v1/hello/')
def hello_world():
    log.error('hello world error')
    result = {
        'code': 200, 
        'error': '',
        'data': {'response': 'hello, world!'},
    }   

    return response_format(result)
