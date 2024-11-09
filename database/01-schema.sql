USE reconmap;

SET
FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS database_migration;

CREATE TABLE database_migration
(
    insert_ts    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    from_version INT UNSIGNED NOT NULL,
    to_version   INT UNSIGNED NOT NULL,

    KEY (from_version, to_version)
);

DROP TABLE IF EXISTS contact;

CREATE TABLE contact
(
    id    INT UNSIGNED                             NOT NULL AUTO_INCREMENT,
    kind  ENUM ('general', 'technical', 'billing') NOT NULL DEFAULT 'general',
    name  VARCHAR(200) NOT NULL,
    email VARCHAR(200) NOT NULL,
    phone VARCHAR(200) NULL,
    role  VARCHAR(200) NULL,

    PRIMARY KEY (id)
) ENGINE = InnoDB
  CHARSET = utf8mb4;

DROP TABLE IF EXISTS user;

CREATE TABLE user
(
    id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    insert_ts     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts     TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    last_login_ts TIMESTAMP NULL COMMENT 'Last login time',
    subject_id    VARCHAR(40)  NOT NULL COMMENT 'JWT sub',
    active        BOOLEAN      NOT NULL DEFAULT TRUE,
    email         VARCHAR(200) NOT NULL,
    role          ENUM ('administrator', 'superuser', 'user', 'client'),
    username      VARCHAR(80)  NOT NULL,
    full_name     VARCHAR(200) NOT NULL,
    short_bio     VARCHAR(1000) NULL,
    timezone      VARCHAR(200) NOT NULL DEFAULT 'UTC',
    mfa_enabled BOOLEAN NOT NULL DEFAULT FALSE,
    preferences   JSON NULL COMMENT 'Client side (eg UI) preferences',

    PRIMARY KEY (id),
    UNIQUE KEY (username)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS audit_log;

CREATE TABLE audit_log
(
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    insert_ts  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id    INT UNSIGNED NULL COMMENT 'Null is system',
    user_agent VARCHAR(250) NULL,
    client_ip  INT UNSIGNED NOT NULL COMMENT 'IPv4 IP',
    action     VARCHAR(200) NOT NULL,
    object     JSON NULL,

    PRIMARY KEY (id),
    CONSTRAINT audit_log_fk_user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
) ENGINE = InnoDB;

DROP TABLE IF EXISTS organisation;

CREATE TABLE organisation
(
    id                       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    insert_ts                TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts                TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    name                     VARCHAR(200) NOT NULL,
    url                      VARCHAR(255) NULL,
    logo_attachment_id       INT UNSIGNED NULL,
    small_logo_attachment_id INT UNSIGNED NULL,

    contact_id               INT UNSIGNED NOT NULL,

    PRIMARY KEY (id),
    UNIQUE KEY (name),
    FOREIGN KEY (logo_attachment_id) REFERENCES attachment (id) ON DELETE SET NULL,
    FOREIGN KEY (small_logo_attachment_id) REFERENCES attachment (id) ON DELETE SET NULL,
    FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE RESTRICT
) ENGINE = InnoDB;

DROP TABLE IF EXISTS client;

CREATE TABLE client
(
    id                       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    insert_ts                TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts                TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    creator_uid              INT UNSIGNED NOT NULL,
    name                     VARCHAR(80) NOT NULL COMMENT 'eg Company name',
    address                  VARCHAR(400) NULL COMMENT 'eg 1 Hacker Way, Menlo Park, California',
    url                      VARCHAR(255) NULL,
    logo_attachment_id       INT UNSIGNED NULL,
    small_logo_attachment_id INT UNSIGNED NULL,

    PRIMARY KEY (id),
    UNIQUE KEY (name),
    FOREIGN KEY (creator_uid) REFERENCES user (id) ON DELETE NO ACTION,
    FOREIGN KEY (logo_attachment_id) REFERENCES attachment (id) ON DELETE SET NULL,
    FOREIGN KEY (small_logo_attachment_id) REFERENCES attachment (id) ON DELETE SET NULL
) ENGINE = InnoDB;



DROP TABLE IF EXISTS client_contact;

CREATE TABLE client_contact
(
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    client_id  INT UNSIGNED NOT NULL,
    contact_id INT UNSIGNED NOT NULL,

    PRIMARY KEY (id),
    UNIQUE KEY (client_id, contact_id),
    FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE,
    FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE CASCADE
) ENGINE = InnoDB;

DROP TABLE IF EXISTS vault;

CREATE TABLE vault
(
    id         INT UNSIGNED                           NOT NULL AUTO_INCREMENT,
    insert_ts  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts  TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    name       VARCHAR(200)  NOT NULL,
    value      VARCHAR(2000) NOT NULL,
    reportable BOOLEAN       NOT NULL,
    note       VARCHAR(1000) NULL,
    type       ENUM ('password','note','token','key') NOT NULL,
    project_id INT UNSIGNED                           NOT NULL,
    record_iv  BLOB          NOT NULL,

    PRIMARY KEY (id),
    UNIQUE KEY (project_id, name),
    KEY (reportable),
    FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE
) Engine = InnoDB;

DROP TABLE IF EXISTS project_category;

CREATE TABLE project_category
(
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name        VARCHAR(200) NOT NULL,
    description VARCHAR(2000) NULL,

    PRIMARY KEY (id),
    UNIQUE KEY (name)
) ENGINE = InnoDB
  CHARSET = utf8mb4;

DROP TABLE IF EXISTS project;

CREATE TABLE project
(
    id                    INT UNSIGNED               NOT NULL AUTO_INCREMENT,
    insert_ts             TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts             TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    creator_uid           INT UNSIGNED               NOT NULL,
    client_id             INT UNSIGNED               NULL COMMENT 'Null when project is template',
    category_id           INT UNSIGNED               NULL,
    is_template           BOOLEAN      NOT NULL DEFAULT FALSE,
    visibility            ENUM ('public', 'private') NOT NULL DEFAULT 'public',
    name                  VARCHAR(200) NOT NULL,
    description           VARCHAR(2000) NULL,
    engagement_start_date DATE,
    engagement_end_date   DATE,
    archived              BOOLEAN      NOT NULL DEFAULT FALSE,
    archive_ts            TIMESTAMP NULL,
    external_id           VARCHAR(40) NULL,
    vulnerability_metrics ENUM ('CVSS', 'OWASP_RR')  NULL,

    PRIMARY KEY (id),
    UNIQUE KEY (name),
    KEY (is_template),
    FOREIGN KEY (creator_uid) REFERENCES user (id) ON DELETE NO ACTION,
    CONSTRAINT project_fk_client_id FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE SET NULL,
    CONSTRAINT project_fk_category_id FOREIGN KEY (category_id) REFERENCES project_category (id) ON DELETE SET NULL
) ENGINE = InnoDB;

DROP VIEW IF EXISTS project_template;

CREATE VIEW project_template
AS
SELECT id, insert_ts, update_ts, creator_uid, name, description, category_id
FROM project
WHERE is_template = 1;

DROP TRIGGER IF EXISTS project_archive_ts_trigger;

CREATE TRIGGER project_archive_ts_trigger
    BEFORE UPDATE
    ON project
    FOR EACH ROW SET NEW.archive_ts = IF(NEW.archived, CURRENT_TIMESTAMP, NULL);

DROP TABLE IF EXISTS project_user;

CREATE TABLE project_user
(
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    insert_ts  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    project_id INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED NOT NULL,

    PRIMARY KEY (id),
    UNIQUE KEY (project_id, user_id),
    FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
);

DROP TABLE IF EXISTS target;

CREATE TABLE target
(
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    parent_id  INT UNSIGNED NULL,
    project_id INT UNSIGNED NOT NULL,
    insert_ts  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts  TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    name       VARCHAR(200) NOT NULL,
    kind       ENUM ('hostname', 'ip_address', 'port', 'cidr_range', 'url', 'binary', 'path', 'file'),
    tags       JSON NULL,

    PRIMARY KEY (id),
    UNIQUE KEY (project_id, name),
    FOREIGN KEY (parent_id) REFERENCES target (id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE
) ENGINE = InnoDB;

DROP TABLE IF EXISTS vulnerability_category;

CREATE TABLE vulnerability_category
(
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    insert_ts   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts   TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    parent_id   INT UNSIGNED  NULL,
    name        VARCHAR(200) NOT NULL,
    description VARCHAR(2000) NULL,

    PRIMARY KEY (id),
    UNIQUE KEY (name),
    KEY (parent_id),
    FOREIGN KEY (parent_id) REFERENCES vulnerability_category (id) ON DELETE CASCADE
) ENGINE = InnoDB;

DROP TABLE IF EXISTS vulnerability;

CREATE TABLE vulnerability
(
    id                     INT UNSIGNED                                                                                       NOT NULL AUTO_INCREMENT,
    insert_ts              TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts              TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    creator_uid            INT UNSIGNED                                                                                       NOT NULL,

    is_template            BOOLEAN      NOT NULL DEFAULT FALSE,

    external_id            VARCHAR(50) NULL COMMENT 'External reference eg RMAP-CLIENT-001',
    project_id             INT UNSIGNED                                                                                       NULL,
    target_id              INT UNSIGNED                                                                                       NULL,
    category_id            INT UNSIGNED                                                                                       NULL,

    summary                VARCHAR(500) NOT NULL,
    description            TEXT NULL,
    external_refs          TEXT NULL,

    visibility             ENUM ('private', 'public')                                                                         NOT NULL DEFAULT 'public',

    risk                   ENUM ('none', 'low', 'medium', 'high', 'critical')                                                 NOT NULL,
    proof_of_concept       TEXT NULL,
    impact                 TEXT NULL,
    remediation            TEXT NULL,
    remediation_complexity ENUM ('unknown', 'low', 'medium', 'high')                                                          NULL,
    remediation_priority   ENUM ('low','medium','high')                                                                       NULL,

    cvss_score             DECIMAL(3, 1) NULL,
    cvss_vector            VARCHAR(80) NULL,
    status                 ENUM ('open', 'confirmed', 'resolved', 'closed')                                                   NOT NULL DEFAULT 'open',
    substatus              ENUM ('reported', 'unresolved', 'unexploited', 'exploited', 'remediated', 'mitigated', 'rejected') NULL     DEFAULT 'reported',
    tags                   JSON NULL,
    owasp_vector           VARCHAR(80) NULL,
    owasp_likehood         DECIMAL(5, 3) NULL,
    owasp_impact           DECIMAL(5, 3) NULL,
    owasp_overall          ENUM ('critical','high','medium','low','note')                                                     NULL,
    custom_fields JSON NULL,

    PRIMARY KEY (id),
    UNIQUE KEY (project_id, target_id, summary),
    KEY (is_template),
    FOREIGN KEY (creator_uid) REFERENCES user (id) ON DELETE NO ACTION,
    CONSTRAINT vulnerability_fk_project_id FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE,
    FOREIGN KEY (target_id) REFERENCES target (id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES vulnerability_category (id) ON DELETE SET NULL
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
    creator_uid  INT UNSIGNED                                        NOT NULL,
    assignee_uid INT UNSIGNED                                        NULL,
    insert_ts    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts    TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    priority     ENUM ('highest', 'high', 'medium', 'low', 'lowest') NOT NULL,
    summary      VARCHAR(200) NOT NULL,
    description  VARCHAR(2000) NULL,
    status       ENUM ('todo', 'doing', 'done')                      NOT NULL DEFAULT 'todo',
    due_date     DATE NULL,
    command_id   INT UNSIGNED                                        NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE,
    FOREIGN KEY (creator_uid) REFERENCES user (id) ON DELETE NO ACTION,
    FOREIGN KEY (assignee_uid) REFERENCES user (id) ON DELETE SET NULL,
    FOREIGN KEY (command_id) REFERENCES command (id) ON DELETE SET NULL
) ENGINE = InnoDB;

DROP TABLE IF EXISTS command;

CREATE TABLE command
(
    id            INT UNSIGNED           NOT NULL AUTO_INCREMENT,
    creator_uid   INT UNSIGNED           NOT NULL,
    insert_ts     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts     TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    name          VARCHAR(200) NOT NULL,
    description   VARCHAR(2000) NULL,
    more_info_url VARCHAR(200) NULL,
    tags          JSON NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (creator_uid) REFERENCES user (id) ON DELETE NO ACTION
) ENGINE = InnoDB;

DROP TABLE IF EXISTS command_usage;

CREATE TABLE command_usage
(
    id              INT UNSIGNED           NOT NULL AUTO_INCREMENT,
    command_id      INT UNSIGNED            NOT NULL,
    creator_uid     INT UNSIGNED           NOT NULL,
    insert_ts       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts       TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    name            VARCHAR(2000) NULL,
    description     VARCHAR(2000) NULL,
    tags            JSON NULL,
    executable_path VARCHAR(255) NULL,
    docker_image    VARCHAR(300) NULL,
    arguments       VARCHAR(2000) NULL,
    output_filename VARCHAR(100) NULL,
    output_parser   VARCHAR(100) NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (creator_uid) REFERENCES user (id) ON DELETE NO ACTION,
    FOREIGN KEY (command_id) REFERENCES command (id) ON DELETE CASCADE
) ENGINE = InnoDB;

INSERT INTO command(id, creator_uid, name)
VALUES (1, 1, 'nmap');

TRUNCATE TABLE command_usage;
INSERT INTO command_usage(id, creator_uid, command_id, description, executable_path, arguments, output_filename,
                          output_parser)
VALUES (1, 1, 1, "Scan all reserved TCP ports on the machine. The -v option enables verbose mode.", "nmap",
        "  -v scanme.nmap.org", "STDOUT", "nmap"),
       (2, 1, 1,
        "Launches a stealth SYN scan against each machine that is up out of the 256 IPs on the /24 sized network where Scanme resides. It also tries to determine what operating system is running on each host that is up and running. This requires root privileges because of the SYN scan and OS detection.",
        "nmap", " -sS -O scanme.nmap.org/24", "STDOUT", "nmap");

DROP TABLE IF EXISTS command_schedule;

CREATE TABLE command_schedule
(
    id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    creator_uid     INT UNSIGNED  NOT NULL,
    insert_ts       TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts       TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    command_id      INT UNSIGNED  NULL,
    argument_values VARCHAR(1000) NULL,
    cron_expression VARCHAR(60) NOT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (command_id) REFERENCES command (id) ON DELETE CASCADE
) ENGINE = InnoDB;

DROP TABLE IF EXISTS report;

CREATE TABLE report
(
    id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id          INT UNSIGNED NULL COMMENT 'Templates have project id NULL',
    generated_by_uid    INT UNSIGNED NOT NULL,
    insert_ts           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    is_template         BOOLEAN      NOT NULL DEFAULT FALSE,

    version_name        VARCHAR(50)  NOT NULL COMMENT 'eg 1.0, 202103',
    version_description VARCHAR(300) NOT NULL COMMENT 'eg Initial, Reviewed, In progress, Draft, Final',

    PRIMARY KEY (id),
    FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE,
    FOREIGN KEY (generated_by_uid) REFERENCES user (id) ON DELETE NO ACTION,
    KEY (is_template)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS report_configuration;

CREATE TABLE report_configuration
(
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id INT UNSIGNED NOT NULL,

    PRIMARY KEY (id),
    UNIQUE (project_id),
    FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE
) ENGINE = InnoDB;

DROP TABLE IF EXISTS custom_field;

CREATE TABLE custom_field (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    insert_ts   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts   TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    parent_type ENUM ('vulnerability') NOT NULL,
    name VARCHAR(100) NOT NULL,
    label VARCHAR(100) NOT NULL,
    kind ENUM('text', 'integer', 'decimal') NOT NULL,
    config JSON NOT NULL,

    PRIMARY KEY (id),
    UNIQUE KEY(parent_type, name)
) ENGINE = InnoDB;

# INSERT INTO custom_field (parent_type, name, label, kind, config) VALUES ('vulnerability', 'cost_pounds', 'Cost in pounds', 'text', '{}');

DROP TABLE IF EXISTS document;

CREATE TABLE document
(
    id          INT UNSIGNED                                 NOT NULL AUTO_INCREMENT,
    insert_ts   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts   TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    user_id     INT UNSIGNED                                 NOT NULL,
    parent_type ENUM ('library', 'project', 'vulnerability') NOT NULL,
    parent_id   INT UNSIGNED                                 NULL,
    visibility  ENUM ('private', 'public')                   NOT NULL DEFAULT 'private',
    title       VARCHAR(250) NULL,
    content     TEXT      NOT NULL,

    PRIMARY KEY (id),
    INDEX (parent_type, parent_id),
    FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE NO ACTION
) ENGINE = InnoDB;

DROP TABLE IF EXISTS note;

CREATE TABLE note
(
    id          INT UNSIGNED                      NOT NULL AUTO_INCREMENT,
    insert_ts   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id     INT UNSIGNED                      NOT NULL,
    parent_type ENUM ('project', 'vulnerability') NOT NULL,
    parent_id   INT UNSIGNED                      NOT NULL,
    visibility  ENUM ('private', 'public')        NOT NULL DEFAULT 'private',
    content     TEXT      NOT NULL,

    PRIMARY KEY (id),
    INDEX (parent_type, parent_id),
    FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE NO ACTION
) ENGINE = InnoDB;

DROP TABLE IF EXISTS attachment;

CREATE TABLE attachment
(
    id               INT UNSIGNED                                                                             NOT NULL AUTO_INCREMENT,
    insert_ts        TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    parent_type      ENUM ('project', 'report', 'command', 'task', 'vulnerability', 'organisation', 'client') NOT NULL,
    parent_id        INT UNSIGNED                                                                             NOT NULL,
    submitter_uid    INT UNSIGNED                                                                             NOT NULL,
    client_file_name VARCHAR(200)   NOT NULL,
    file_name        VARCHAR(200)   NOT NULL,
    file_size        INT UNSIGNED                                                                             NOT NULL,
    file_mimetype    VARCHAR(200) NULL,
    file_hash        VARCHAR(10000) NOT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (submitter_uid) REFERENCES user (id) ON DELETE NO ACTION
) ENGINE = InnoDB;

DROP FUNCTION IF EXISTS PARENT_CHILD_NAME;

DELIMITER $$

CREATE FUNCTION PARENT_CHILD_NAME(
    parent_name VARCHAR (100),
    child_name VARCHAR (100)
)
    RETURNS VARCHAR(202)
    DETERMINISTIC
BEGIN
    IF
parent_name IS NULL THEN
        RETURN child_name;
END IF;
RETURN CONCAT(parent_name, ', ', child_name);
END$$

DELIMITER ;

DROP TABLE IF EXISTS notification;

CREATE TABLE notification
(
    id         INT UNSIGNED            NOT NULL AUTO_INCREMENT,
    insert_ts  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts  TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    to_user_id INT UNSIGNED            NOT NULL,
    title      VARCHAR(200) NULL,
    content    VARCHAR(4000) NOT NULL,
    status     ENUM ('unread', 'read') NOT NULL DEFAULT 'unread',

    PRIMARY KEY (id),
    FOREIGN KEY (to_user_id) REFERENCES user (id) ON DELETE CASCADE
) ENGINE = InnoDB
  CHARSET = utf8mb4;

SET
FOREIGN_KEY_CHECKS = 1;
