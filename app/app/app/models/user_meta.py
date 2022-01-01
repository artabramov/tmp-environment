from app import db


class UserMeta(db.Model):
    __tablename__ = 'users_meta'
    __table_args__ = (db.UniqueConstraint('user_id', 'meta_key', name='users_meta_ukey'),)
    id = db.Column(db.BigInteger, primary_key=True)
    created_date = db.Column(db.DateTime(timezone=False), server_default=db.func.now(), nullable=False)
    updated_date = db.Column(db.DateTime(timezone=False), server_default='1970-01-01 00:00:00', server_onupdate=db.func.now(), nullable=False)
    user_id = db.Column(db.BigInteger, db.ForeignKey('users.id'), index=True)
    meta_key = db.Column(db.String(20), index=True, nullable=False)
    meta_value = db.Column(db.String(255))

