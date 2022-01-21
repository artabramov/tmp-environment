from flask import request
import logging


class LoggerWrapper():
    def __init__(self, app):
        self.logger = self._set_logger(app)

    def _set_logger(self, app):
        while app.logger.hasHandlers():
            app.logger.removeHandler(app.logger.handlers[0])
            
        handler = logging.handlers.TimedRotatingFileHandler(app.config['LOG_FILENAME'], 
            when=app.config['LOG_ROTATE_WHEN'], 
            backupCount=app.config['LOG_BACKUP_COUNT'])
        handler.setFormatter(logging.Formatter(app.config['LOG_FORMAT']))
        app.logger.addHandler(handler)
        return app.logger

    @property
    def extra(self):
        return {
            'method': request.method,
            'url': request.url}

    def debug(self, msg, *args, **kwargs):
        self.logger = logging.LoggerAdapter(self.logger, self.extra)
        self.logger.debug(msg, *args, **kwargs)

    def info(self, msg, *args, **kwargs):
        self.logger = logging.LoggerAdapter(self.logger, self.extra)
        self.logger.info(msg, *args, **kwargs)

    def warning(self, msg, *args, **kwargs):
        self.logger = logging.LoggerAdapter(self.logger, self.extra)
        self.logger.warning(msg, *args, **kwargs)

    def error(self, msg, *args, **kwargs):
        self.logger = logging.LoggerAdapter(self.logger, self.extra)
        self.logger.error(msg, *args, **kwargs)

    def critical(self, msg, *args, **kwargs):
        self.logger = logging.LoggerAdapter(self.logger, self.extra)
        self.logger.critical(msg, *args, **kwargs)
