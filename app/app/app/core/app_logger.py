#import logging
import os
#from datetime import datetime

import logging
from logging.handlers import TimedRotatingFileHandler

def app_logger(app):

    """
    class DebugFileHandler(logging.FileHandler):
        def emit(self, record):
            super(DebugFileHandler, self).emit(record)
    
    handler = DebugFileHandler(app.config['LOGGING_PATH'] + datetime.now().strftime(app.config['LOGGING_FILE']))

    handler.setFormatter(logging.Formatter(app.config['LOGGING_FORMATTER']))
    """
    
    """
    while app.logger.hasHandlers():
        app.logger.removeHandler(app.logger.handlers[0])

    handler = TimedRotatingFileHandler('/var/log/app/app.log', when='M', interval=1, backupCount=5)
    handler.setLevel(logging.INFO)
    handler.setFormatter(logging.Formatter(app.config['LOGGING_FORMATTER']))
    app.logger.addHandler(handler)
    """
    
    for handler in app.logger.handlers:
        handler.setFormatter(logging.Formatter(app.config['LOGGING_FORMATTER']))

