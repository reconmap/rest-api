SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE project
    ADD CONSTRAINT project_fk_client_id FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE SET NULL;

SET FOREIGN_KEY_CHECKS = 1;
