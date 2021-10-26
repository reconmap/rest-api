ALTER TABLE vulnerability_category
    ADD COLUMN parent_id INT UNSIGNED NULL REFERENCES vulnerability_category AFTER update_ts,
    ADD KEY (parent_id);

DROP FUNCTION IF EXISTS PARENT_CHILD_NAME;

DELIMITER $$

CREATE FUNCTION PARENT_CHILD_NAME(
    parent_name VARCHAR(100),
    child_name VARCHAR(100)
)
    RETURNS VARCHAR(100)
    DETERMINISTIC
BEGIN
    IF parent_name IS NULL THEN
        RETURN child_name;
    END IF;
    RETURN CONCAT(parent_name, ', ', child_name);
END$$

DELIMITER ;
