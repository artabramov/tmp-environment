from app import db
import enum
import werkzeug
import re
import hashlib


EMAIL_REGEX = re.compile(r"[^@]+@[^@]+\.[^@]+")
PASS_REGEX = re.compile(r"(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!#%*?&]{6,24}$")
NAME_REGEX = re.compile(r"^[a-zA-Z0-9_-]+$")
PASS_SALT = 'w3ry-sEcreT-pa$$-Salt!'


class UserStatus(enum.Enum):
    pending = 1
    approved = 2
    trash = 3


class User(db.Model):
    __tablename__ = 'users'
    __user_pass = None
    id = db.Column(db.BigInteger, primary_key=True)
    created_date = db.Column(db.DateTime(timezone=False), server_default=db.func.now(), nullable=False)
    updated_date = db.Column(db.DateTime(timezone=False), server_default='1970-01-01 00:00:00', server_onupdate=db.func.now(), nullable=False)
    deleted_date = db.Column(db.DateTime(timezone=False), server_default='1970-01-01 00:00:00', nullable=False)
    reminded_date = db.Column(db.DateTime(timezone=False), server_default='1970-01-01 00:00:00', nullable=False)
    user_status = db.Column(db.Enum(UserStatus))
    user_email = db.Column(db.String(255), index=True, unique=True)
    pass_hash = db.Column(db.String(128), index=True, nullable=False)
    user_name = db.Column(db.String(80))
    user_meta = db.relationship('UserMeta', backref='users')

    #def __init__(self, *args, **kwargs):
    #    self.user_pass = kwargs.get('user_pass', None)

    @property
    def user_pass(self):
        return self.__user_pass

    @user_pass.setter
    def user_pass(self, value):
        if not value:
            raise werkzeug.exceptions.BadRequest('user_pass is empty')
        elif not re.search(PASS_REGEX, value):
            raise werkzeug.exceptions.BadRequest('user_pass is incorrect')
        self.__user_pass = value
        user_pass_encoded = self.__user_pass.encode()
        hash_obj = hashlib.sha512(user_pass_encoded)
        self.pass_hash = hash_obj.hexdigest()

    @db.validates('user_email')
    def validate_user_email(self, key, value):
        if key == 'user_email' and not value:
            raise werkzeug.exceptions.BadRequest('user_email is empty')
        elif key == 'user_email'  and not EMAIL_REGEX.match(value):
            raise werkzeug.exceptions.BadRequest('user_email is incorrect')
        return value
        
    @db.validates('user_name')
    def validate_user_name(self, key, value):
        if key == 'user_name' and not value:
            raise werkzeug.exceptions.BadRequest('user_name is empty')
        elif key == 'user_name'  and not NAME_REGEX.match(value):
            raise werkzeug.exceptions.BadRequest('user_name is incorrect')
        return value

