from app import db

class Msg(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    msg = db.Column(db.String(80), unique=False, nullable=False)
