CREATE TABLE vault
(
    id         INT UNSIGNED                           NOT NULL AUTO_INCREMENT,
    insert_ts  TIMESTAMP                              NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_ts  TIMESTAMP                              NULL ON UPDATE CURRENT_TIMESTAMP,
    name       VARCHAR(200)                           NOT NULL,
    value      VARCHAR(2000)                          NOT NULL,
    reportable BOOLEAN                                NOT NULL,
    note       VARCHAR(1000)                          NULL,
    type       ENUM ('password','note','token','key') NOT NULL,
    project_id INT UNSIGNED                           NOT NULL REFERENCES project,
    record_iv  BLOB                                   NOT NULL,

    PRIMARY KEY (id),
    UNIQUE KEY (project_id, name),
    KEY (reportable)
) Engine = InnoDB;
