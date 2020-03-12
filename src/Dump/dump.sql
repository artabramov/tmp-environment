
SET sql_mode = '';

CREATE DATABASE IF NOT EXISTS slim CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS slim.users (
    id              BIGINT(20)   NOT NULL AUTO_INCREMENT,
    date            DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
    user_status     VARCHAR(40)  NOT NULL, # pending | approved | trash
    user_login      VARCHAR(40)  NOT NULL,
    user_token      VARCHAR(40)  NOT NULL,
    user_token_date DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00', # Дата создания текущего токена
    user_email      VARCHAR(255) NOT NULL,
    user_phone      VARCHAR(20)  NOT NULL,
    user_hash       VARCHAR(40)  NOT NULL,
    user_hash_date  DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00', # Дата создания текущего пароля

    PRIMARY KEY id              (id),
            KEY date            (date),
            KEY user_status     (user_status),
            KEY user_login      (user_login),
            KEY user_token      (user_token),
            KEY user_token_date (user_token_date),
            KEY user_email      (user_email),
            KEY user_phone      (user_phone),
            KEY user_hash       (user_hash),
            KEY user_hash_date  (user_hash_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS slim.usermeta (
    id         BIGINT(20)   NOT NULL AUTO_INCREMENT,
    date       DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
    user_id    BIGINT(20)   NOT NULL,
    meta_key   VARCHAR(40)  NOT NULL,
    meta_value VARCHAR(255) NOT NULL,
        
    PRIMARY KEY id         (id),
            KEY date       (date),
            KEY user_id    (user_id),
            KEY meta_key   (meta_key),
            KEY meta_value (meta_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS slim.posts (
    id            BIGINT(20)   NOT NULL AUTO_INCREMENT,
    date          DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
    user_id       BIGINT(20)   NOT NULL,
    parent_id     BIGINT(20)   NOT NULL,

    post_type     VARCHAR(40)  NOT NULL, # task | event
    post_status   VARCHAR(40)  NOT NULL, # draft | private | public | custom | trash  OR  inherit | trash
    post_excerpt  VARCHAR(255) NOT NULL,
    post_content  TEXT         NOT NULL,

    posts_count   BIGINT(20)  NOT NULL, # child posts
    amounts_count BIGINT(20)  NOT NULL, # current + childs
    amounts_sum   FLOAT(20,8) NOT NULL, # current + childs
    uploads_count BIGINT(20)  NOT NULL, # current + childs
    uploads_size  FLOAT(20,8) NOT NULL, # current + childs

    PRIMARY KEY id            (id),
            KEY created_date  (created_date),
            KEY updated_date  (updated_date),
            KEY creator_id    (creator_id),
            KEY parent_id     (parent_id),

            KEY post_type     (post_type),
            KEY post_status   (post_status),
            KEY post_excerpt  (post_excerpt),

            KEY posts_count   (posts_count),
            KEY amounts_count (amounts_count),
            KEY amounts_sum   (amounts_sum),
            KEY uploads_count (uploads_count),
            KEY uploads_size  (uploads_size)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS slim.members (
    id           BIGINT(20)  NOT NULL AUTO_INCREMENT,
    date         DATETIME    NOT NULL DEFAULT '0000-00-00 00:00:00',
    user_id      BIGINT(20)  NOT NULL,
    parent_id    BIGINT(20)  NOT NULL,

    member_id    BIGINT(20)  NOT NULL, 
    member_role  VARCHAR(40) NOT NULL, # editor | reader
        
    PRIMARY KEY id           (id),
            KEY created_date (created_date),
            KEY updated_date (updated_date),
            KEY creator_id   (creator_id),
            KEY parent_id    (parent_id),

            KEY member_id    (member_id),
            KEY member_role  (member_role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS slim.terms (
    id           BIGINT(20)   NOT NULL AUTO_INCREMENT,
    created_date DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
    updated_date DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
    creator_id   BIGINT(20)   NOT NULL,
    parent_id    BIGINT(20)   NOT NULL,

    term_key     VARCHAR(40)  NOT NULL,
    term_value   VARCHAR(255) NOT NULL,
        
    PRIMARY KEY id           (id),
            KEY created_date (created_date),
            KEY updated_date (updated_date),
            KEY creator_id   (creator_id),
            KEY parent_id    (parent_id),

            KEY term_key     (term_key),
            KEY term_value   (term_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# * * * * * *


CREATE TABLE IF NOT EXISTS slim.amounts (
    id           BIGINT(20)  NOT NULL AUTO_INCREMENT,
    date         DATETIME    NOT NULL DEFAULT '0000-00-00 00:00:00',
    user_id      BIGINT(20)  NOT NULL,
    post_id      BIGINT(20)  NOT NULL,
    amount_key   VARCHAR(40) NOT NULL,
    amount_value FLOAT(20,8) NOT NULL,
        
    PRIMARY KEY id           (id),
            KEY date         (date),
            KEY user_id      (user_id),
            KEY post_id      (post_id),
            KEY amount_key   (amount_key),
            KEY amount_value (amount_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS slim.uploads (
    id           BIGINT(20)   NOT NULL AUTO_INCREMENT,
    date         DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
    user_id      BIGINT(20)   NOT NULL,
    post_id      BIGINT(20)   NOT NULL,
    upload_key   VARCHAR(40)  NOT NULL,
    upload_mime  VARCHAR(255) NOT NULL,
    upload_size  BIGINT(20)   NOT NULL,
    upload_path  VARCHAR(255) NOT NULL,
    upload_file  TEXT         NOT NULL,

    PRIMARY KEY id           (id),
            KEY date         (date),
            KEY user_id      (user_id),
            KEY post_id      (post_id),
            KEY upload_key   (upload_key),
            KEY upload_mime  (upload_mime),
            KEY upload_size  (upload_size),
            KEY upload_path  (upload_path)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS slim.timers (
    id          BIGINT(20)  NOT NULL AUTO_INCREMENT,
    date        DATETIME    NOT NULL DEFAULT '0000-00-00 00:00:00',
    user_id     BIGINT(20)  NOT NULL,
    post_id     BIGINT(20)  NOT NULL,
    timer_key   VARCHAR(40) NOT NULL,
    timer_value DATETIME    NOT NULL DEFAULT '0000-00-00 00:00:00',
        
    PRIMARY KEY id          (id),
            KEY date        (date),
            KEY user_id     (user_id),
            KEY post_id     (post_id),
            KEY timer_key   (timer_key),
            KEY timer_value (timer_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
