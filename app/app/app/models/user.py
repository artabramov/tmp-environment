from app import app
from app import db
import enum
import werkzeug
import re
import hashlib


class UserStatus(enum.Enum):
    pending = 1
    approved = 2
    trash = 3


class User(db.Model):
    EMAIL_REGEX = re.compile(r"^[a-z0-9._-]{2,122}@[a-z0-9._-]{2,122}\.[a-z]{2,10}$")
    PASS_REGEX = re.compile(r"(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!#%*?&]{6,40}$")
    NAME_REGEX = re.compile(r"^[^\s]{1}[a-zA-Z0-9 ]{4,38}[^\s]{1}$")

    __tablename__ = 'users'
    __user_password = ''
    id = db.Column(db.BigInteger, primary_key=True)
    created_date = db.Column(db.DateTime(timezone=False), server_default=db.func.now(), nullable=False)
    updated_date = db.Column(db.DateTime(timezone=False), server_default='1970-01-01 00:00:00', server_onupdate=db.func.now(), nullable=False)
    deleted_date = db.Column(db.DateTime(timezone=False), server_default='1970-01-01 00:00:00', nullable=False)
    user_status = db.Column(db.Enum(UserStatus))
    user_email = db.Column(db.String(255), index=True, unique=True)
    user_name = db.Column(db.String(40))
    password_hash = db.Column(db.String(64), nullable=False)

    user_authcode = db.relationship('UserAuthcode', backref='users')
    user_token = db.relationship('UserToken', backref='users')
    user_meta = db.relationship('UserMeta', backref='users')

    def __init__(self, user_email, user_password, user_name, user_status='pending'):
        self.user_status = user_status
        self.user_email = user_email
        self.user_password = user_password
        self.user_name = user_name

    @property
    def user_password(self):
        return self.__user_password

    @user_password.setter
    def user_password(self, value):
        if not value:
            raise werkzeug.exceptions.BadRequest('user_password is empty')
        elif not re.search(self.PASS_REGEX, value):
            raise werkzeug.exceptions.BadRequest('user_password is incorrect')
        self.__user_password = value
        self.password_hash = self._get_hash(value)

    def _get_hash(self, value):
        value = value + app.config['USER_PASSWORD_SALT']
        encoded_pass = value.encode()
        hash_obj = hashlib.sha256(encoded_pass)
        return hash_obj.hexdigest()

    @db.validates('user_email')
    def validate_user_email(self, key, value):
        if key == 'user_email' and not value:
            raise werkzeug.exceptions.BadRequest('user_email is empty')
        elif key == 'user_email' and not self.EMAIL_REGEX.match(value):
            raise werkzeug.exceptions.BadRequest('user_email is incorrect')
        return value
        
    @db.validates('user_name')
    def validate_user_name(self, key, value):
        if key == 'user_name' and not value:
            raise werkzeug.exceptions.BadRequest('user_name is empty')
        elif key == 'user_name'  and not self.NAME_REGEX.match(value):
            raise werkzeug.exceptions.BadRequest('user_name is incorrect')
        return value
