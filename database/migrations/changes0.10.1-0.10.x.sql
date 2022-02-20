ALTER TABLE project
    ADD COLUMN vulnerability_metrics ENUM ('CVSS', 'OWASP_RR') NULL;

ALTER TABLE vulnerability
    ADD COLUMN owasp_vector   VARCHAR(80)                                    NULL,
    ADD COLUMN owasp_likehood DECIMAL(5, 3)                                  NULL,
    ADD COLUMN owasp_impact   DECIMAL(5, 3)                                  NULL,
    ADD COLUMN owasp_overall  ENUM ('critical','high','medium','low','note') NULL;

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

CREATE TABLE client_contact
(
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    client_id  INT UNSIGNED NOT NULL REFERENCES client,
    contact_id INT UNSIGNED NOT NULL REFERENCES contact,

    PRIMARY KEY (id),
    UNIQUE KEY (client_id, contact_id)
) ENGINE = InnoDB;
