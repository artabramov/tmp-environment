SET sql_mode = '';


# Пользователи
CREATE TABLE IF NOT EXISTS project.users (
    id          BIGINT(20)   NOT NULL AUTO_INCREMENT,
    date        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_status VARCHAR(40)  NOT NULL, # pending / approved / trash
    user_token  VARCHAR(40)  NOT NULL, # User token (unique)
    user_email  VARCHAR(255) NOT NULL, # User email
    user_hash   VARCHAR(40)  NOT NULL, # One-time password hash (non-unique)

    PRIMARY KEY id          (id),
            KEY date        (date),
            KEY user_status (user_status),
    UNIQUE  KEY user_token  (user_token),
    UNIQUE  KEY user_email  (user_email),
            KEY user_hash   (user_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Метаданные пользователей
CREATE TABLE IF NOT EXISTS project.usermeta (
    id         BIGINT(20)   NOT NULL AUTO_INCREMENT,
    date       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id    BIGINT(20)   NOT NULL,
    meta_key   VARCHAR(40)  NOT NULL,
    meta_value VARCHAR(255) NOT NULL,

    PRIMARY KEY id         (id),
            KEY date       (date),
            KEY user_id    (user_id),
            KEY meta_key   (meta_key),
            KEY meta_value (meta_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Хабы
CREATE TABLE IF NOT EXISTS project.hubs (
    id         BIGINT(20)   NOT NULL AUTO_INCREMENT,
    date       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id    BIGINT(20)   NOT NULL, # Создатель группы
    hub_status VARCHAR(40)  NOT NULL, # private / public / trash
    hub_name   VARCHAR(255) NOT NULL, # Название группы

    PRIMARY KEY id         (id),
            KEY date       (date),
            KEY user_id    (user_id),
            KEY hub_status (hub_status),
            KEY hub_name   (hub_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Метаданные хабов
CREATE TABLE IF NOT EXISTS project.hubmeta (
    id         BIGINT(20)   NOT NULL AUTO_INCREMENT,
    date       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    hub_id     BIGINT(20)   NOT NULL,
    meta_key   VARCHAR(40)  NOT NULL,
    meta_value VARCHAR(255) NOT NULL,

    PRIMARY KEY id         (id),
            KEY date       (date),
            KEY hub_id     (hub_id),
            KEY meta_key   (meta_key),
            KEY meta_value (meta_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Записи
CREATE TABLE IF NOT EXISTS project.posts (
    id           BIGINT(20)  NOT NULL AUTO_INCREMENT,
    date         DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    parent_id    BIGINT(20)  NOT NULL,
    user_id      BIGINT(20)  NOT NULL,
    hub_id       BIGINT(20)  NOT NULL,
    post_status  VARCHAR(40) NOT NULL, # task: todo / active / done / trash, comment: inherit / trash
    post_type    VARCHAR(40) NOT NULL, # task / comment
    post_content TEXT        NOT NULL,

    PRIMARY KEY id          (id),
            KEY date        (date),
            KEY parent_id   (parent_id),
            KEY user_id     (user_id),
            KEY hub_id      (hub_id),
            KEY post_status (post_status),
            KEY post_type   (post_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Метаданные записей
CREATE TABLE IF NOT EXISTS project.postmeta (
    id         BIGINT(20)   NOT NULL AUTO_INCREMENT,
    date       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    post_id    BIGINT(20)   NOT NULL,
    meta_key   VARCHAR(40)  NOT NULL,
    meta_value VARCHAR(255) NOT NULL,

    PRIMARY KEY id         (id),
            KEY date       (date),
            KEY post_id    (post_id),
            KEY meta_key   (meta_key),
            KEY meta_value (meta_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Загруженные файлы
CREATE TABLE IF NOT EXISTS project.uploads (
    id          BIGINT(20)   NOT NULL AUTO_INCREMENT,
    date        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    post_id     BIGINT(20)   NOT NULL,
    upload_name VARCHAR(255) NOT NULL,
    upload_mime VARCHAR(255) NOT NULL,
    upload_size BIGINT(20)   NOT NULL,
    upload_file VARCHAR(255) NOT NULL,

    PRIMARY KEY id          (id),
            KEY date        (date),
            KEY post_id     (post_id),
            KEY upload_name (upload_name),
            KEY upload_mime (upload_mime),
            KEY upload_size (upload_size),
            KEY upload_file (upload_file)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
