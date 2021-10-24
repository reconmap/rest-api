ALTER TABLE vulnerability_category
    ADD COLUMN parent_id INT UNSIGNED NULL REFERENCES vulnerability_category AFTER update_ts,
    ADD KEY (parent_id);
