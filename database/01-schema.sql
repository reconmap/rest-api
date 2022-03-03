SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS contact;

CREATE TABLE contact
(
    id    INT UNSIGNED                             NOT NULL AUTO_INCREMENT,
    kind  ENUM ('general', 'technical', 'billing') NOT NULL DEFAULT 'general',
    name  VARCHAR(200)                             NOT NULL,
    email VARCHAR(200)                             NOT NULL,
    phone VARCHAR(200)                             NULL,
    role  VARCHAR(200)                             NULL,

    PRIMARY KEY (id)
) ENGINE = InnoDB
  CHARSET = utf8mb4;

DROP TABLE IF EXISTS user;

CREATE TABLE user
(
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    insert_ts   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts   TIMESTAMP     NULL ON UPDATE CURRENT_TIMESTAMP,
    active      BOOLEAN       NOT NULL DEFAULT TRUE,
    email       VARCHAR(200)  NOT NULL,
    role        ENUM ('administrator', 'superuser', 'user', 'client'),
    username    VARCHAR(80)   NOT NULL,
    password    VARCHAR(255)  NOT NULL COMMENT 'Hashed password',
    full_name   VARCHAR(200)  NOT NULL,
    short_bio   VARCHAR(1000) NULL,
    timezone    VARCHAR(200)  NOT NULL DEFAULT 'UTC',
    preferences JSON          NULL COMMENT 'Client side (eg UI) preferences',
    mfa_enabled BOOLEAN       NOT NULL DEFAULT FALSE,
    mfa_secret  VARCHAR(100)  NULL,

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
    id                       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    insert_ts                TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts                TIMESTAMP    NULL ON UPDATE CURRENT_TIMESTAMP,
    name                     VARCHAR(200) NOT NULL,
    url                      VARCHAR(255) NULL,
    logo_attachment_id       INT UNSIGNED NULL REFERENCES attachment ON DELETE SET NULL,
    small_logo_attachment_id INT UNSIGNED NULL REFERENCES attachment ON DELETE SET NULL,

    contact_id               INT UNSIGNED NOT NULL REFERENCES contact,

    PRIMARY KEY (id),
    UNIQUE KEY (name)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS client;

CREATE TABLE client
(
    id                       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    insert_ts                TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts                TIMESTAMP    NULL ON UPDATE CURRENT_TIMESTAMP,
    creator_uid              INT UNSIGNED NOT NULL REFERENCES user,
    name                     VARCHAR(80)  NOT NULL COMMENT 'eg Company name',
    address                  VARCHAR(400) NULL COMMENT 'eg 1 Hacker Way, Menlo Park, California',
    url                      VARCHAR(255) NULL,
    logo_attachment_id       INT UNSIGNED NULL REFERENCES attachment ON DELETE SET NULL,
    small_logo_attachment_id INT UNSIGNED NULL REFERENCES attachment ON DELETE SET NULL,

    PRIMARY KEY (id),
    UNIQUE KEY (name)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS client_contact;

CREATE TABLE client_contact
(
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    client_id  INT UNSIGNED NOT NULL REFERENCES client ON DELETE CASCADE,
    contact_id INT UNSIGNED NOT NULL REFERENCES contact ON DELETE CASCADE,

    PRIMARY KEY (id),
    UNIQUE KEY (client_id, contact_id)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS vault;

CREATE TABLE vault
(
    id              INT UNSIGNED                            NOT NULL AUTO_INCREMENT,
    insert_ts       TIMESTAMP                               NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts       TIMESTAMP                               NULL ON UPDATE CURRENT_TIMESTAMP,
    name            VARCHAR(200)                            NOT NULL,
    value           VARCHAR(2000)                           NOT NULL,
    reportable      BOOLEAN                                 NOT NULL,
    note            VARCHAR(1000)                           NULL,
    type            ENUM ('password','note','token','key')  NOT NULL,
    project_id      INT UNSIGNED                            NOT NULL REFERENCES project,
    record_iv       BLOB                                    NOT NULL,

    PRIMARY KEY (id),
    UNIQUE KEY (project_id, name),
    KEY (reportable)  
) Engine = InnoDB;

DROP TABLE IF EXISTS project;

CREATE TABLE project
(
    id                    INT UNSIGNED                             NOT NULL AUTO_INCREMENT,
    insert_ts             TIMESTAMP                                NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts             TIMESTAMP                                NULL ON UPDATE CURRENT_TIMESTAMP,
    creator_uid           INT UNSIGNED                             NOT NULL REFERENCES user,
    client_id             INT UNSIGNED                             NULL COMMENT 'Null when project is template' REFERENCES client,
    is_template           BOOLEAN                                  NOT NULL DEFAULT FALSE,
    visibility            ENUM ('public', 'private')               NOT NULL DEFAULT 'public',
    name                  VARCHAR(200)                             NOT NULL,
    description           VARCHAR(2000)                            NULL,
    engagement_type       ENUM ('blackbox', 'whitebox', 'greybox') NULL,
    engagement_start_date DATE,
    engagement_end_date   DATE,
    archived              BOOLEAN                                  NOT NULL DEFAULT FALSE,
    archive_ts            TIMESTAMP                                NULL,
    external_id           VARCHAR(40)                              NULL,
    vulnerability_metrics ENUM ('CVSS', 'OWASP_RR')                NULL,

    PRIMARY KEY (id),
    UNIQUE KEY (name),
    KEY (is_template)
) ENGINE = InnoDB;

DROP VIEW IF EXISTS project_template;

CREATE VIEW project_template
AS
SELECT id, insert_ts, update_ts, creator_uid, name, description, engagement_type
FROM project
WHERE is_template = 1;

DROP TRIGGER IF EXISTS project_archive_ts_trigger;

CREATE TRIGGER project_archive_ts_trigger
    BEFORE UPDATE
    ON project
    FOR EACH ROW
    SET NEW.archive_ts = IF(NEW.archived, CURRENT_TIMESTAMP, NULL);

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
    parent_id  INT UNSIGNED NULL REFERENCES target,
    project_id INT UNSIGNED NOT NULL REFERENCES project,
    insert_ts  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts  TIMESTAMP    NULL ON UPDATE CURRENT_TIMESTAMP,
    name       VARCHAR(200) NOT NULL,
    kind       ENUM ('hostname', 'ip_address', 'port', 'cidr_range', 'url', 'binary', 'path', 'file'),
    tags       JSON         NULL,

    PRIMARY KEY (id),
    UNIQUE KEY (project_id, name)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS vulnerability_category;

CREATE TABLE vulnerability_category
(
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    insert_ts   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts   TIMESTAMP     NULL ON UPDATE CURRENT_TIMESTAMP,
    parent_id   INT UNSIGNED  NULL REFERENCES vulnerability_category,
    name        VARCHAR(200)  NOT NULL,
    description VARCHAR(2000) NULL,

    PRIMARY KEY (id),
    UNIQUE KEY (name),
    KEY (parent_id)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS vulnerability;

CREATE TABLE vulnerability
(
    id                     INT UNSIGNED                                                                                       NOT NULL AUTO_INCREMENT,
    insert_ts              TIMESTAMP                                                                                          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts              TIMESTAMP                                                                                          NULL ON UPDATE CURRENT_TIMESTAMP,
    creator_uid            INT UNSIGNED                                                                                       NOT NULL REFERENCES user,

    is_template            BOOLEAN                                                                                            NOT NULL DEFAULT FALSE,

    external_id            VARCHAR(50)                                                                                        NULL COMMENT 'External reference eg RMAP-CLIENT-001',
    project_id             INT UNSIGNED                                                                                       NULL REFERENCES project,
    target_id              INT UNSIGNED                                                                                       NULL REFERENCES target,
    category_id            INT UNSIGNED                                                                                       NULL REFERENCES vulnerability_category,

    summary                VARCHAR(500)                                                                                       NOT NULL,
    description            TEXT                                                                                               NULL,
    external_refs          TEXT                                                                                               NULL,

    visibility             ENUM ('private', 'public')                                                                         NOT NULL DEFAULT 'public',

    risk                   ENUM ('none', 'low', 'medium', 'high', 'critical')                                                 NOT NULL,
    proof_of_concept       TEXT                                                                                               NULL,
    impact                 TEXT                                                                                               NULL,
    remediation            TEXT                                                                                               NULL,
    remediation_complexity ENUM ('unknown', 'low', 'medium', 'high')                                                          NULL,
    remediation_priority   ENUM ('low','medium','high')                                                                       NULL,

    cvss_score             DECIMAL(3, 1)                                                                                      NULL,
    cvss_vector            VARCHAR(80)                                                                                        NULL,
    status                 ENUM ('open', 'confirmed', 'resolved', 'closed')                                                   NOT NULL DEFAULT 'open',
    substatus              ENUM ('reported', 'unresolved', 'unexploited', 'exploited', 'remediated', 'mitigated', 'rejected') NULL     DEFAULT 'reported',
    tags                   JSON                                                                                               NULL,
    owasp_vector           VARCHAR(80)                                                                                        NULL,
    owasp_likehood         DECIMAL(5, 3)                                                                                      NULL,
    owasp_impact           DECIMAL(5, 3)                                                                                      NULL,
    owasp_overall          ENUM ('critical','high','medium','low','note')                                                     NULL,

    PRIMARY KEY (id),
    UNIQUE KEY (project_id, target_id, summary),
    KEY (is_template)
) ENGINE = InnoDB;

DROP VIEW IF EXISTS vulnerability_template;
CREATE VIEW vulnerability_template AS
SELECT id,
       creator_uid,
       category_id,
       insert_ts,
       update_ts,
       summary,
       description,
       proof_of_concept,
       impact,
       remediation,
       risk,
       cvss_score,
       cvss_vector,
       tags,
       owasp_vector,
       owasp_likehood,
       owasp_impact,
       owasp_overall
FROM vulnerability
WHERE is_template = 1;

DROP TABLE IF EXISTS task;

CREATE TABLE task
(
    id           INT UNSIGNED                                        NOT NULL AUTO_INCREMENT,
    project_id   INT UNSIGNED                                        NOT NULL,
    creator_uid  INT UNSIGNED                                        NOT NULL REFERENCES user,
    assignee_uid INT UNSIGNED                                        NULL REFERENCES user,
    insert_ts    TIMESTAMP                                           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts    TIMESTAMP                                           NULL ON UPDATE CURRENT_TIMESTAMP,
    priority     ENUM ('highest', 'high', 'medium', 'low', 'lowest') NOT NULL,
    summary      VARCHAR(200)                                        NOT NULL,
    description  VARCHAR(2000)                                       NULL,
    status       ENUM ('todo', 'doing', 'done')                      NOT NULL DEFAULT 'todo',
    due_date     DATE                                                NULL,
    command_id   INT UNSIGNED                                        NULL REFERENCES command,

    PRIMARY KEY (id),
    FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE
) ENGINE = InnoDB;

DROP TABLE IF EXISTS command;

CREATE TABLE command
(
    id              INT UNSIGNED           NOT NULL AUTO_INCREMENT,
    creator_uid     INT UNSIGNED           NOT NULL REFERENCES user,
    insert_ts       TIMESTAMP              NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts       TIMESTAMP              NULL ON UPDATE CURRENT_TIMESTAMP,
    name            VARCHAR(200)           NOT NULL,
    description     VARCHAR(2000)          NULL,
    output_parser   VARCHAR(100)           NULL,
    executable_type ENUM ('custom','rmap') NOT NULL DEFAULT 'custom',
    executable_path VARCHAR(255)           NULL,
    docker_image    VARCHAR(300)           NULL,
    arguments       VARCHAR(240)           NULL,
    configuration   JSON                   NULL,
    output_filename VARCHAR(100)           NULL,
    more_info_url   VARCHAR(200)           NULL,
    tags            JSON                   NULL,

    PRIMARY KEY (id)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS report;

CREATE TABLE report
(
    id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id          INT UNSIGNED NOT NULL REFERENCES project,
    generated_by_uid    INT UNSIGNED NOT NULL REFERENCES user,
    insert_ts           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    is_template         BOOLEAN      NOT NULL DEFAULT FALSE,

    version_name        VARCHAR(50)  NOT NULL COMMENT 'eg 1.0, 202103',
    version_description VARCHAR(300) NOT NULL COMMENT 'eg Initial, Reviewed, In progress, Draft, Final',

    PRIMARY KEY (id),
    KEY (is_template)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS report_configuration;

CREATE TABLE report_configuration
(
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id INT UNSIGNED NOT NULL REFERENCES project,

    PRIMARY KEY (id),
    UNIQUE (project_id)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS document;

CREATE TABLE document
(
    id          INT UNSIGNED                                 NOT NULL AUTO_INCREMENT,
    insert_ts   TIMESTAMP                                    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts   TIMESTAMP                                    NULL ON UPDATE CURRENT_TIMESTAMP,
    user_id     INT UNSIGNED                                 NOT NULL REFERENCES user,
    parent_type ENUM ('library', 'project', 'vulnerability') NOT NULL,
    parent_id   INT UNSIGNED                                 NULL,
    visibility  ENUM ('private', 'public')                   NOT NULL DEFAULT 'private',
    title       VARCHAR(250)                                 NULL,
    content     TEXT                                         NOT NULL,

    PRIMARY KEY (id),
    INDEX (parent_type, parent_id)
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
    id               INT UNSIGNED                                                                             NOT NULL AUTO_INCREMENT,
    insert_ts        TIMESTAMP                                                                                NOT NULL DEFAULT CURRENT_TIMESTAMP,
    parent_type      ENUM ('project', 'report', 'command', 'task', 'vulnerability', 'organisation', 'client') NOT NULL,
    parent_id        INT UNSIGNED                                                                             NOT NULL,
    submitter_uid    INT UNSIGNED                                                                             NOT NULL REFERENCES user,
    client_file_name VARCHAR(200)                                                                             NOT NULL,
    file_name        VARCHAR(200)                                                                             NOT NULL,
    file_size        INT UNSIGNED                                                                             NOT NULL,
    file_mimetype    VARCHAR(200)                                                                             NULL,
    file_hash        VARCHAR(10000)                                                                           NOT NULL,

    PRIMARY KEY (id)
) ENGINE = InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

DROP FUNCTION IF EXISTS PARENT_CHILD_NAME;

DELIMITER $$

CREATE FUNCTION PARENT_CHILD_NAME(
    parent_name VARCHAR(100),
    child_name VARCHAR(100)
)
    RETURNS VARCHAR(202)
    DETERMINISTIC
BEGIN
    IF parent_name IS NULL THEN
        RETURN child_name;
    END IF;
    RETURN CONCAT(parent_name, ', ', child_name);
END$$

DELIMITER ;

DROP TABLE IF EXISTS notification;

CREATE TABLE notification
(
    id         INT UNSIGNED            NOT NULL AUTO_INCREMENT,
    insert_ts  TIMESTAMP               NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts  TIMESTAMP               NULL ON UPDATE CURRENT_TIMESTAMP,
    to_user_id INT UNSIGNED            NOT NULL REFERENCES user,
    title      VARCHAR(200)            NULL,
    content    VARCHAR(4000)           NOT NULL,
    status     ENUM ('unread', 'read') NOT NULL DEFAULT 'unread',

    PRIMARY KEY (id)
) ENGINE = InnoDB
  CHARSET = utf8mb4;
