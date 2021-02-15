SET sql_mode = '';


# Пользователи
CREATE TABLE IF NOT EXISTS project.users (
    id         BIGINT(20)   NOT NULL AUTO_INCREMENT,
    date       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status     VARCHAR(40)  NOT NULL, # pending / approved / trash
    user_token VARCHAR(40)  NOT NULL, # Уникальный токен
    user_email VARCHAR(255) NOT NULL, # Электронная почта
    user_hash  VARCHAR(40)  NOT NULL, # Одноразовый пароль для авторизации

    PRIMARY KEY id         (id),
            KEY date       (date),
            KEY status     (status),
    UNIQUE  KEY user_token (user_token),
    UNIQUE  KEY user_email (user_email),
            KEY user_hash  (user_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Группы пользователей
CREATE TABLE IF NOT EXISTS project.users_groups (
    id           BIGINT(20)   NOT NULL AUTO_INCREMENT,
    date         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status       VARCHAR(40)  NOT NULL, # private / public / trash
    user_id      BIGINT(20)   NOT NULL, # Создатель группы
    group_name   VARCHAR(255) NOT NULL, # Название группы

    PRIMARY KEY id         (id),
            KEY date       (date),
            KEY status     (status),
            KEY user_id    (user_id),
            KEY group_name (group_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Метаданные пользователей
CREATE TABLE IF NOT EXISTS project.users_meta (
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


# Записи
CREATE TABLE IF NOT EXISTS project.posts (
    id           BIGINT(20)  NOT NULL AUTO_INCREMENT,
    date         DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status       VARCHAR(40) NOT NULL,
    user_id      BIGINT(20)  NOT NULL,
    group_id     BIGINT(20)  NOT NULL,
    parent_id    BIGINT(20)  NOT NULL,
    post_type    VARCHAR(40) NOT NULL, # post / comment / task
    post_content TEXT        NOT NULL,

    PRIMARY KEY id        (id),
            KEY date      (date),
            KEY status    (status),
            KEY user_id   (user_id),
            KEY group_id  (group_id),
            KEY parent_id (parent_id),
            KEY post_type (post_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Метаданные постов
CREATE TABLE IF NOT EXISTS project.posts_meta (
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
CREATE TABLE IF NOT EXISTS project.posts_uploads (
    id           BIGINT(20)   NOT NULL AUTO_INCREMENT,
    date         DATETIME     NOT NULL DEFAULT '0000-00-00 00:00:00',
    post_id      BIGINT(20)   NOT NULL,

    upload_key   VARCHAR(40)  NOT NULL,
    upload_name  VARCHAR(255) NOT NULL,
    upload_mime  VARCHAR(255) NOT NULL,
    upload_size  BIGINT(20)   NOT NULL,
    upload_file  VARCHAR(255) NOT NULL,

    PRIMARY KEY id           (id),
            KEY date         (date),
            KEY post_id      (post_id),

            KEY upload_key   (upload_key),
            KEY upload_name  (upload_name),
            KEY upload_mime  (upload_mime),
            KEY upload_size  (upload_size),
            KEY upload_file  (upload_file)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Суммы
CREATE TABLE IF NOT EXISTS project.posts_amounts (
    id           BIGINT(20)    NOT NULL AUTO_INCREMENT,
    date         DATETIME      NOT NULL DEFAULT '0000-00-00 00:00:00',
    post_id      BIGINT(20)    NOT NULL,
    amount_key   VARCHAR(40)   NOT NULL,
    amount_value DECIMAL(24,4) NOT NULL,
        
    PRIMARY KEY id           (id),
            KEY date         (date),
            KEY post_id      (post_id),
            KEY amount_key   (amount_key),
            KEY amount_value (amount_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Временные метки
CREATE TABLE IF NOT EXISTS project.posts_timers (
    id          BIGINT(20)  NOT NULL AUTO_INCREMENT,
    date        DATETIME    NOT NULL DEFAULT '0000-00-00 00:00:00',
    post_id     BIGINT(20)  NOT NULL,
    timer_key   VARCHAR(40) NOT NULL,
    timer_value DATETIME    NOT NULL DEFAULT '0000-00-00 00:00:00',
        
    PRIMARY KEY id          (id),
            KEY date        (date),
            KEY post_id     (post_id),
            KEY timer_key   (timer_key),
            KEY timer_value (timer_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
