SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE user
    ADD COLUMN subject_id VARCHAR(40) NOT NULL COMMENT 'JWT sub' AFTER update_ts,
    DROP COLUMN password,
    DROP COLUMN mfa_enabled,
    DROP COLUMN mfa_secret;

SET FOREIGN_KEY_CHECKS = 1;
