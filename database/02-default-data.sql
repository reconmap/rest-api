TRUNCATE TABLE user;
INSERT INTO user (id, full_name, username, password, email, role)
VALUES (1, 'Administrator', 'admin', '$2y$10$CrblfxMv8e1ynu9RG54Cau.8dcmz.SpT7nNERclfGMZSbYHoQQuuq', 'admin@localhost',
        'administrator');

TRUNCATE TABLE audit_log;
INSERT INTO audit_log (user_id, client_ip, action)
VALUES (0, INET_ATON('127.0.0.1'), 'Initialised system');

TRUNCATE TABLE vulnerability_category;
INSERT INTO vulnerability_category (name, description)
VALUES ('Access Controls', 'Related to authorization of users, and assessment of rights.'),
       ('Auditing and Logging', 'Related to auditing of actions, or logging of problems.'),
       ('Authentication', 'Related to the identification of users.'),
       ('Configuration', 'Related to security configurations of servers, devices, or software.'),
       ('Cryptography', 'Related to mathematical protections for data.'),
       ('Data Exposure', 'Related to unintended exposure of sensitive information.'),
       ('Data Validation', 'Related to improper reliance on the structure or values of data.'),
       ('Denial of Service', 'Related to causing system failure.'),
       ('Error Reporting', 'Related to the reporting of error conditions in a secure fashion.'),
       ('Patching', 'Related to keeping software up to date.'),
       ('Session Management', 'Related to the identification of authenticated users.'),
       ('Timing', 'Related to race conditions, locking, or order of operations.');

TRUNCATE TABLE organisation;
INSERT INTO organisation (name, url, contact_email)
VALUES ('Reconmap default org', 'https://reconmap.org', 'no-reply@reconmap.org');

INSERT INTO report (project_id, generated_by_uid, is_template, version_name, version_description)
VALUES (0, 0, 1, 'Default', 'Default report template');

INSERT INTO attachment (parent_type, parent_id, submitter_uid, client_file_name, file_name, file_size, file_mimetype,
                        file_hash)
VALUES ('report', LAST_INSERT_ID(), 0, 'default-report-template.docx', 'default-report-template.docx', 0,
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '');
