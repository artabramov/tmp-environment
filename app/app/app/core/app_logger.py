import logging

def app_logger(app):
    for handler in app.logger.handlers:
        handler.setFormatter(logging.Formatter(app.config['LOGGING_FORMATTER']))
