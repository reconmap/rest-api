ALTER TABLE project
    ADD COLUMN vulnerability_metrics ENUM ('CVSS', 'OWASP_RR') NULL;
ALTER TABLE vulnerability 
    ADD COLUMN owasp_vector VARCHAR(80) NULL,
    ADD COLUMN owasp_likehood DECIMAL(5, 3) NULL,
    ADD COLUMN owasp_impact DECIMAL(5, 3) NULL,
    ADD COLUMN owasp_overall ENUM ('critical','high','medium','low','note') NULL;
ALTER TABLE attachment
    MODIFY COLUMN parent_type ENUM ('project', 'report', 'command', 'task', 'vulnerability', 'organisation', 'client') NOT NULL;
ALTER TABLE organisation
    ADD COLUMN logo_attachment_id        INT UNSIGNED NULL REFERENCES attachment,
    ADD COLUMN small_logo_attachment_id  INT UNSIGNED NULL REFERENCES attachment;
ALTER TABLE client
    ADD COLUMN logo_attachment_id        INT UNSIGNED NULL REFERENCES attachment,
    ADD COLUMN small_logo_attachment_id  INT UNSIGNED NULL REFERENCES attachment;