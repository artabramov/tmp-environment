import logging

def logger_format(app):
    for handler in app.logger.handlers:
        handler.setFormatter(logging.Formatter(app.config['LOGGING_FORMATTER']))
