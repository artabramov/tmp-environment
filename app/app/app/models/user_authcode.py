from app import db


class UserAuthcode(db.Model):
    __tablename__ = 'users_authcodes'
    id = db.Column(db.BigInteger, primary_key=True)
    created_date = db.Column(db.DateTime(timezone=False), server_default=db.func.now(), nullable=False)
    updated_date = db.Column(db.DateTime(timezone=False), server_default='1970-01-01 00:00:00', server_onupdate=db.func.now(), nullable=False)
    expired_date = db.Column(db.DateTime(timezone=False), nullable=False)
    user_id = db.Column(db.BigInteger, db.ForeignKey('users.id'), index=True)
    authcode_hash = db.Column(db.String(64), nullable=True)

