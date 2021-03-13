SET sql_mode = '';


# Users
CREATE TABLE IF NOT EXISTS project.users (
    id          BIGINT(20)   NOT NULL AUTO_INCREMENT,
    date        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_status VARCHAR(40)  NOT NULL DEFAULT '', # pending | approved | trash
    user_token  VARCHAR(80)  NOT NULL, # Unique user token
    user_email  VARCHAR(255) NOT NULL,
    user_hash   VARCHAR(40)  NOT NULL DEFAULT '', # One-time password hash

    PRIMARY KEY id          (id),
            KEY date        (date),
            KEY user_status (user_status),
    UNIQUE  KEY user_token  (user_token),
    UNIQUE  KEY user_email  (user_email),
            KEY user_hash   (user_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Users attributes
CREATE TABLE IF NOT EXISTS project.user_attribs (
    id           BIGINT(20)   NOT NULL AUTO_INCREMENT,
    date         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id      BIGINT(20)   NOT NULL,
    attrib_key   VARCHAR(40)  NOT NULL,
    attrib_value VARCHAR(255) NOT NULL,

    PRIMARY KEY id           (id),
            KEY date         (date),
            KEY user_id      (user_id),
            KEY attrib_key   (attrib_key),
            KEY attrib_value (attrib_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Users groups
CREATE TABLE IF NOT EXISTS project.groups (
    id           BIGINT(20)   NOT NULL AUTO_INCREMENT,
    date         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id      BIGINT(20)   NOT NULL,
    group_status VARCHAR(40)  NOT NULL, # private | public | trash
    group_name   VARCHAR(255) NOT NULL,

    PRIMARY KEY id           (id),
            KEY date         (date),
            KEY user_id      (user_id),
            KEY group_status (group_status),
            KEY group_name   (group_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# User roles
CREATE TABLE IF NOT EXISTS project.user_roles (
    id        BIGINT(20)  NOT NULL AUTO_INCREMENT,
    date      DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id   BIGINT(20)  NOT NULL,
    group_id  BIGINT(20)  NOT NULL,
    user_role VARCHAR(40) NOT NULL, # admin | editor | reader | invited

    PRIMARY KEY id        (id),
            KEY date      (date),
            KEY user_id   (user_id),
            KEY group_id  (group_id),
            KEY user_role (user_role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Posts
CREATE TABLE IF NOT EXISTS project.posts (
    id           BIGINT(20)  NOT NULL AUTO_INCREMENT,
    date         DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id      BIGINT(20)  NOT NULL,
    group_id     BIGINT(20)  NOT NULL,
    parent_id    BIGINT(20)  NOT NULL,
    post_status  VARCHAR(40) NOT NULL, # parent: todo | doing | done | trash, child: inherit | trash
    post_type    VARCHAR(40) NOT NULL, # document | comment
    post_content TEXT        NOT NULL,

    PRIMARY KEY id          (id),
            KEY date        (date),
            KEY user_id     (user_id),
            KEY group_id    (group_id),
            KEY parent_id   (parent_id),
            KEY post_status (post_status),
            KEY post_type   (post_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Posts tags
CREATE TABLE IF NOT EXISTS project.post_tags (
    id        BIGINT(20)   NOT NULL AUTO_INCREMENT,
    date      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id   BIGINT(20)   NOT NULL,
    post_id   BIGINT(20)   NOT NULL,
    tag_key   VARCHAR(40)  NOT NULL,
    tag_value VARCHAR(255) NOT NULL,

    PRIMARY KEY id        (id),
            KEY date      (date),
            KEY user_id   (user_id),
            KEY post_id   (post_id),
            KEY tag_key   (tag_key),
            KEY tag_value (tag_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Posts uploads
CREATE TABLE IF NOT EXISTS project.post_uploads (
    id            BIGINT(20)   NOT NULL AUTO_INCREMENT,
    date          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id       BIGINT(20)   NOT NULL,
    post_id       BIGINT(20)   NOT NULL,
    upload_status VARCHAR(40)  NOT NULL, # inherit | favorite | trash
    upload_name   VARCHAR(255) NOT NULL, # Filename
    upload_mime   VARCHAR(255) NOT NULL,
    upload_size   BIGINT(20)   NOT NULL,
    upload_file   VARCHAR(255) NOT NULL, # Full link to the file

    PRIMARY KEY id            (id),
            KEY date          (date),
            KEY user_id       (user_id),
            KEY post_id       (post_id),
            KEY upload_status (upload_status),
            KEY upload_name   (upload_name),
            KEY upload_mime   (upload_mime),
            KEY upload_size   (upload_size),
     UNIQUE KEY upload_file   (upload_file)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Posts timers
CREATE TABLE IF NOT EXISTS project.post_timers (
    id          BIGINT(20)  NOT NULL AUTO_INCREMENT,
    date        DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id     BIGINT(20)  NOT NULL,
    post_id     BIGINT(20)  NOT NULL,
    timer_key   VARCHAR(40) NOT NULL,
    timer_value DATETIME    NOT NULL,

    PRIMARY KEY id          (id),
            KEY date        (date),
            KEY user_id     (user_id),
            KEY post_id     (post_id),
            KEY timer_key   (timer_key),
            KEY timer_value (timer_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
