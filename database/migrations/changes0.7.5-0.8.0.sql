ALTER TABLE audit_log
    ADD COLUMN user_agent VARCHAR(250) NULL AFTER user_id;

ALTER TABLE project
    ADD COLUMN engagement_type       ENUM ('blackbox', 'whitebox', 'greybox') NULL,
    ADD COLUMN engagement_start_date DATE,
    ADD COLUMN engagement_end_date   DATE;
