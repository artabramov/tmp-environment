import logging
import os
from datetime import datetime

def app_logger(app):

    class DebugFileHandler(logging.FileHandler):
        def emit(self, record):
            super(DebugFileHandler, self).emit(record)

    debug_file_handler = DebugFileHandler(app.config['LOGGING_PATH'] + datetime.now().strftime(app.config['LOGGING_FILE']))
    debug_file_handler.setFormatter(logging.Formatter(app.config['LOGGING_FORMATTER']))
    app.logger.addHandler(debug_file_handler)
