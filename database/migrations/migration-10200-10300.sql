ALTER TABLE command
    MODIFY COLUMN arguments VARCHAR(2000) NULL;

ALTER TABLE audit_log
    MODIFY COLUMN user_id INT UNSIGNED NULL COMMENT 'Null is system';

ALTER TABLE vulnerability
    DROP FOREIGN KEY vulnerability_ibfk_2,
    ADD CONSTRAINT vulnerability_fk_project_id FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE;
