import logging
import os
from datetime import datetime

def app_logger(app):

    if not os.path.exists(app.config['LOGGING_PATH']):
        os.makedirs(app.config['LOGGING_PATH'])

    class DebugFileHandler(logging.FileHandler):
        def emit(self, record):
            # if your app is configured for debugging
            # and the logger has been set to DEBUG level (the lowest)
            # push the message to the file

            #if app.debug and app.logger.level==logging.DEBUG:
            super(DebugFileHandler, self).emit(record)

    debug_file_handler = DebugFileHandler(app.config['LOGGING_PATH'] + datetime.now().strftime('%d-%m-%Y.log'))

    debug_file_handler.setFormatter(logging.Formatter(
        '[%(asctime)s] %(levelname)s file %(filename)s, line %(lineno)d: %(message)s'
    ))

    app.logger.addHandler(debug_file_handler)
