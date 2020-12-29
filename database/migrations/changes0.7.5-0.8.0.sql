ALTER TABLE audit_log
    ADD COLUMN user_agent VARCHAR(250) NULL AFTER user_id;

