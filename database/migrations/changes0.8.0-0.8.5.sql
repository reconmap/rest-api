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


ALTER TABLE user
	RENAME COLUMN name TO username,
	ADD COLUMN full_name VARCHAR(200) NOT NULL,
	ADD COLUMN short_bio VARCHAR(1000) NULL;

ALTER TABLE audit_log
	MODIFY COLUMN object JSON NULL;

ALTER TABLE client
	ADD COLUMN creator_uid INT UNSIGNED NOT NULL REFERENCES user;

ALTER TABLE project
	ADD COLUMN creator_uid INT UNSIGNED NOT NULL REFERENCES user;

ALTER TABLE vulnerability
	RENAME COLUMN reported_by_uid TO creator_uid;

ALTER TABLE task
	ADD COLUMN creator_uid INT UNSIGNED NOT NULL REFERENCES user,
	DELETE COLUMN completed,
	DELETE COLUMN command,
	DELETE COLUMN command_parser,
	ADD COLUMN status ENUM('todo', 'doing', 'done') NOT NULL DEFAULT 'todo',
	ADD COLUMN command_id INT UNSIGNED NULL REFERENCES command;

RENAME TABLE task_result TO command_output;

ALTER TABLE command_output
	RENAME COLUMN task_id TO command_id,
	RENAME COLUMN output TO file_content,
	ADD COLUMN file_name VARCHAR(200) NOT NULL,
	ADD COLUMN file_size INT UNSIGNED NOT NULL,
	ADD COLUMN file_mimetype VARCHAR(200) NULL;

