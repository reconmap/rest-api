DROP TABLE IF EXISTS user;

CREATE TABLE user
(
    id        INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    insert_ts TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts TIMESTAMP     NULL ON UPDATE CURRENT_TIMESTAMP,
    timezone  VARCHAR(200)  NOT NULL DEFAULT 'UTC',
    full_name VARCHAR(200)  NOT NULL,
    short_bio VARCHAR(1000) NULL,
    email     VARCHAR(200)  NOT NULL,
    role      ENUM ('creator', 'writer', 'reader'),
    username  VARCHAR(80)   NOT NULL,
    password  VARCHAR(255)  NOT NULL COMMENT 'Hashed password',
    PRIMARY KEY (id),
    UNIQUE KEY (username)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS audit_log;

CREATE TABLE audit_log
(
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    insert_ts  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id    INT UNSIGNED NOT NULL COMMENT 'User 0 is system' REFERENCES user,
    user_agent VARCHAR(250) NULL,
    client_ip  INT UNSIGNED NOT NULL COMMENT 'IPv4 IP',
    action     VARCHAR(200) NOT NULL,
    object     JSON         NULL,
    PRIMARY KEY (id)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS organisation;

CREATE TABLE organisation
(
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    insert_ts     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts     TIMESTAMP    NULL ON UPDATE CURRENT_TIMESTAMP,
    name          VARCHAR(200) NOT NULL,
    url           VARCHAR(255) NULL,
    contact_name  VARCHAR(200) NULL,
    contact_email VARCHAR(200) NULL,
    contact_phone VARCHAR(200) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (name)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS client;

CREATE TABLE client
(
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    insert_ts     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts     TIMESTAMP    NULL ON UPDATE CURRENT_TIMESTAMP,
    creator_uid   INT UNSIGNED NOT NULL REFERENCES user,
    name          VARCHAR(80)  NOT NULL COMMENT 'eg Company name',
    url           VARCHAR(255) NULL,
    contact_name  VARCHAR(200) NOT NULL,
    contact_email VARCHAR(200) NOT NULL,
    contact_phone VARCHAR(200) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (name)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS project;

CREATE TABLE project
(
    id                    INT UNSIGNED                             NOT NULL AUTO_INCREMENT,
    insert_ts             TIMESTAMP                                NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts             TIMESTAMP                                NULL ON UPDATE CURRENT_TIMESTAMP,
    creator_uid           INT UNSIGNED                             NOT NULL REFERENCES user,
    client_id             INT UNSIGNED                             NULL COMMENT 'Null when project is template' REFERENCES client,
    is_template           BOOLEAN                                  NOT NULL DEFAULT FALSE,
    name                  VARCHAR(200)                             NOT NULL,
    description           VARCHAR(2000)                            NULL,
    engagement_type       ENUM ('blackbox', 'whitebox', 'greybox') NULL,
    engagement_start_date DATE,
    engagement_end_date   DATE,
    PRIMARY KEY (id),
    UNIQUE KEY (name)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS project_user;

CREATE TABLE project_user
(
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    insert_ts  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    project_id INT UNSIGNED NOT NULL REFERENCES project,
    user_id    INT UNSIGNED NOT NULL REFERENCES user,
    PRIMARY KEY (id),
    UNIQUE KEY (project_id, user_id)
);

DROP TABLE IF EXISTS target;

CREATE TABLE target
(
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id INT UNSIGNED NOT NULL REFERENCES project,
    insert_ts  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts  TIMESTAMP    NULL ON UPDATE CURRENT_TIMESTAMP,
    name       VARCHAR(200) NOT NULL,
    kind       ENUM (
        'hostname',
        'ip_address',
        'cidr_range',
        'url',
        'binary'
        ),
    PRIMARY KEY (id),
    UNIQUE KEY (name)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS vulnerability_category;

CREATE TABLE vulnerability_category
(
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name        VARCHAR(200)  NOT NULL,
    description VARCHAR(2000) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (name)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS vulnerability;

CREATE TABLE vulnerability
(
    id          INT UNSIGNED                                       NOT NULL AUTO_INCREMENT,
    project_id  INT UNSIGNED                                       NOT NULL REFERENCES project,
    target_id   INT UNSIGNED                                       NULL REFERENCES target,
    creator_uid INT UNSIGNED                                       NOT NULL REFERENCES user,
    category_id INT UNSIGNED                                       NULL REFERENCES vulnerability_category,
    insert_ts   TIMESTAMP                                          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts   TIMESTAMP                                          NULL ON UPDATE CURRENT_TIMESTAMP,
    summary     VARCHAR(200)                                       NOT NULL,
    description VARCHAR(2000)                                      NULL,
    risk        ENUM ('none', 'low', 'medium', 'high', 'critical') NOT NULL,
    cvss_score  DECIMAL(2, 1)                                      NULL,
    cvss_vector VARCHAR(80)                                        NULL,
    status      ENUM ('open', 'closed')                            NOT NULL DEFAULT 'open',
    PRIMARY KEY (id)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS task;

CREATE TABLE task
(
    id           INT UNSIGNED                   NOT NULL AUTO_INCREMENT,
    project_id   INT UNSIGNED                   NOT NULL REFERENCES project,
    creator_uid  INT UNSIGNED                   NOT NULL REFERENCES user,
    assignee_uid INT UNSIGNED                   NULL REFERENCES user,
    insert_ts    TIMESTAMP                      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts    TIMESTAMP                      NULL ON UPDATE CURRENT_TIMESTAMP,
    name         VARCHAR(200)                   NOT NULL,
    description  VARCHAR(2000)                  NULL,
    status       ENUM ('todo', 'doing', 'done') NOT NULL DEFAULT 'todo',
    command_id   INT UNSIGNED                   NULL REFERENCES command,
    PRIMARY KEY (id)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS command;

CREATE TABLE command
(
    id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    creator_uid    INT UNSIGNED  NOT NULL REFERENCES user,
    insert_ts      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts      TIMESTAMP     NULL ON UPDATE CURRENT_TIMESTAMP,
    short_name     VARCHAR(200)  NOT NULL,
    description    VARCHAR(2000) NULL,
    docker_image   VARCHAR(300)  NULL,
    container_args VARCHAR(240)  NULL,
    configuration  JSON          NULL,
    PRIMARY KEY (id)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS command_output;

CREATE TABLE command_output
(
    id               INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    command_id       INT UNSIGNED   NOT NULL REFERENCES command,
    submitted_by_uid INT UNSIGNED   NOT NULL REFERENCES user,
    insert_ts        TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    file_name        VARCHAR(200)   NOT NULL,
    file_size        INT UNSIGNED   NOT NULL,
    file_mimetype    VARCHAR(200)   NULL,
    file_content     VARCHAR(10000) NOT NULL,
    PRIMARY KEY (id)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS report;

CREATE TABLE report
(
    id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id          INT UNSIGNED NOT NULL REFERENCES project,
    generated_by_uid    INT UNSIGNED NOT NULL REFERENCES user,
    insert_ts           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    version_name        VARCHAR(50)  NOT NULL COMMENT 'eg 1.0, 202103',
    version_description VARCHAR(300) NOT NULL COMMENT 'eg Initial, Reviewed, In progress, Draft, Final',
    PRIMARY KEY (id)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS note;

CREATE TABLE note
(
    id          INT UNSIGNED                      NOT NULL AUTO_INCREMENT,
    insert_ts   TIMESTAMP                         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id     INT UNSIGNED                      NOT NULL REFERENCES user,
    parent_type ENUM ('project', 'vulnerability') NOT NULL,
    parent_id   INT UNSIGNED                      NOT NULL,
    visibility  ENUM ('private', 'public')        NOT NULL DEFAULT 'private',
    content     TEXT                              NOT NULL,
    PRIMARY KEY (id),
    INDEX (parent_type, parent_id)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS attachment;

CREATE TABLE attachment
(
    id               INT UNSIGNED                NOT NULL AUTO_INCREMENT,
    insert_ts        TIMESTAMP                   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    parent_type      ENUM ('project', 'command') NOT NULL,
    parent_id        INT UNSIGNED                NOT NULL,
    submitter_uid    INT UNSIGNED                NOT NULL REFERENCES user,
    client_file_name VARCHAR(200)                NOT NULL,
    file_name        VARCHAR(200)                NOT NULL,
    file_size        INT UNSIGNED                NOT NULL,
    file_mimetype    VARCHAR(200)                NULL,
    file_hash        VARCHAR(10000)              NOT NULL,
    PRIMARY KEY (id)
) ENGINE = InnoDB;
