from flask import make_response, jsonify

def send_response(data={}):
    response = make_response(
        jsonify(
            {
                'error': '', 
                'success': 'true',
                'data': data
            }
        ),
        200,
    )
    response.headers['Content-Type'] = 'application/json'
    response.headers['Access-Control-Allow-Origin'] = '*'
    response.headers['Server'] = 'noserver'
    return response