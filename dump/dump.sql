SET sql_mode = '';


CREATE TABLE IF NOT EXISTS project.users (
    id          BIGINT(20)   NOT NULL AUTO_INCREMENT,
    date        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_status VARCHAR(20)  NOT NULL DEFAULT '',
    user_token  VARCHAR(80)  NOT NULL,
    user_email  VARCHAR(255) NOT NULL,
    user_hash   VARCHAR(40)  NOT NULL DEFAULT '',

    PRIMARY KEY id          (id),
            KEY date        (date),
            KEY user_status (user_status),
    UNIQUE  KEY user_token  (user_token),
    UNIQUE  KEY user_email  (user_email),
            KEY user_hash   (user_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS project.user_attributes (
    id              BIGINT(20)   NOT NULL AUTO_INCREMENT,
    date            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id         BIGINT(20)   NOT NULL,
    attribute_key   VARCHAR(20)  NOT NULL,
    attribute_value VARCHAR(255) NOT NULL,

    PRIMARY KEY id              (id),
            KEY date            (date),
            KEY user_id         (user_id),
            KEY attribute_key   (attribute_key),
            KEY attribute_value (attribute_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS project.user_roles (
    id        BIGINT(20)  NOT NULL AUTO_INCREMENT,
    date      DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id   BIGINT(20)  NOT NULL,
    group_id  BIGINT(20)  NOT NULL,
    user_role VARCHAR(20) NOT NULL, # admin | editor | reader | invited

    PRIMARY KEY id        (id),
            KEY date      (date),
            KEY user_id   (user_id),
            KEY group_id  (group_id),
            KEY user_role (user_role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS project.groups (
    id           BIGINT(20)   NOT NULL AUTO_INCREMENT,
    date         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id      BIGINT(20)   NOT NULL,
    group_status VARCHAR(20)  NOT NULL, # private | public | trash
    group_name   VARCHAR(255) NOT NULL,

    PRIMARY KEY id           (id),
            KEY date         (date),
            KEY user_id      (user_id),
            KEY group_status (group_status),
            KEY group_name   (group_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS project.posts (
    id           BIGINT(20)  NOT NULL AUTO_INCREMENT,
    date         DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id      BIGINT(20)  NOT NULL,
    group_id     BIGINT(20)  NOT NULL,
    parent_id    BIGINT(20)  NOT NULL,
    post_status  VARCHAR(20) NOT NULL, # parent: todo | doing | done | trash, child: inherit | trash
    post_type    VARCHAR(20) NOT NULL, # document | comment
    post_content TEXT        NOT NULL,

    PRIMARY KEY id          (id),
            KEY date        (date),
            KEY user_id     (user_id),
            KEY group_id    (group_id),
            KEY parent_id   (parent_id),
            KEY post_status (post_status),
            KEY post_type   (post_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS project.post_meta (
    id         BIGINT(20)   NOT NULL AUTO_INCREMENT,
    date       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id    BIGINT(20)   NOT NULL,
    post_id    BIGINT(20)   NOT NULL,
    meta_key   VARCHAR(20)  NOT NULL,
    meta_value VARCHAR(255) NOT NULL,

    PRIMARY KEY id         (id),
            KEY date       (date),
            KEY user_id    (user_id),
            KEY post_id    (post_id),
            KEY meta_key   (meta_key),
            KEY meta_value (meta_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS project.post_uploads (
    id            BIGINT(20)   NOT NULL AUTO_INCREMENT,
    date          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id       BIGINT(20)   NOT NULL,
    post_id       BIGINT(20)   NOT NULL,
    upload_status VARCHAR(20)  NOT NULL, # inherit | favorite | trash
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
