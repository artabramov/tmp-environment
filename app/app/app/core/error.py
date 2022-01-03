from app.core.response import send_response
import werkzeug
from app import app

@app.errorhandler(werkzeug.exceptions.BadRequest)
@app.errorhandler(werkzeug.exceptions.Unauthorized)
def send_error(e):
    #logger.error(e.args)
    #pass
    #return send_response(success=False, status=e.code, error=e.description)
    return {'error': 'ERROR!'}
    return {
        'success': False, 
        'status': e.code, 
        'error': e.description,
        'data': {},
    }