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

ALTER TABLE task
    RENAME COLUMN description TO command,
    RENAME COLUMN parser TO command_parser,
    ADD COLUMN description VARCHAR(2000);
