from app import db


class UserToken(db.Model):
    __tablename__ = 'users_tokens'
    id = db.Column(db.Integer, primary_key=True)
    created_date = db.Column(db.DateTime(timezone=False), server_default=db.func.now(), nullable=False)
    updated_date = db.Column(db.DateTime(timezone=False), server_default='1970-01-01 00:00:00', server_onupdate=db.func.now(), nullable=False)
    expired_date = db.Column(db.DateTime(timezone=False), nullable=False)
    refresh_token = db.Column(db.String(80), index=True, unique=True)

