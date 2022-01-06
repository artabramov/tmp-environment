from flask import make_response, jsonify

def app_response(status, error, data):
    response = make_response(
        jsonify(
            {
                'error': error,
                'data': data
            }
        ),
        status,
    )
    response.headers['Content-Type'] = 'application/json'
    response.headers['Access-Control-Allow-Origin'] = '*'
    response.headers['Server'] = 'noserver'
    return response