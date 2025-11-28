ALTER TABLE user
    ADD last_login_ts TIMESTAMP NULL COMMENT 'Last login time' AFTER update_ts;
