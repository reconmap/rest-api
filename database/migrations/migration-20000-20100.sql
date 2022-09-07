SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE command_schedule
(
    id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    creator_uid     INT UNSIGNED  NOT NULL,
    insert_ts       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts       TIMESTAMP     NULL ON UPDATE CURRENT_TIMESTAMP,
    command_id      INT UNSIGNED  NULL,
    argument_values VARCHAR(1000) NULL,
    cron_expression VARCHAR(60)   NOT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (command_id) REFERENCES command (id) ON DELETE CASCADE
) ENGINE = InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
