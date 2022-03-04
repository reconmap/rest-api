TRUNCATE TABLE user;
INSERT INTO user (id, full_name, username, password, email, role)
VALUES (1, 'Administrator', 'admin', '$2y$10$CrblfxMv8e1ynu9RG54Cau.8dcmz.SpT7nNERclfGMZSbYHoQQuuq', 'admin@localhost',
        'administrator');

TRUNCATE TABLE audit_log;
INSERT INTO audit_log (user_id, client_ip, action)
VALUES (0, INET_ATON('127.0.0.1'), 'Initialised system');

TRUNCATE TABLE database_migration;
INSERT INTO database_migration(from_version, to_version)
VALUES (0, 10000);

TRUNCATE TABLE vulnerability_category;
INSERT INTO vulnerability_category (id, parent_id, name, description)
VALUES (1,NULL,'General', 'General categories.'),
       (2,1,'Access Controls', 'Related to authorization of users, and assessment of rights.'),
       (3,1,'Auditing and Logging', 'Related to auditing of actions, or logging of problems.'),
       (4,1,'Authentication', 'Related to the identification of users.'),
       (5,1,'Configuration', 'Related to security configurations of servers, devices, or software.'),
       (6,1,'Cryptography', 'Related to mathematical protections for data.'),
       (7,1,'Data Exposure', 'Related to unintended exposure of sensitive information.'),
       (8,1,'Data Validation', 'Related to improper reliance on the structure or values of data.'),
       (9,1,'Denial of Service', 'Related to causing system failure.'),
       (10,1,'Error Reporting', 'Related to the reporting of error conditions in a secure fashion.'),
       (11,1,'Patching', 'Related to keeping software up to date.'),
       (12,1,'Session Management', 'Related to the identification of authenticated users.'),
       (13,1,'Timing', 'Related to race conditions, locking, or order of operations.'),
       (14,NULL,'OWASP WSTG','Categories based on OWASP Web Security Testing Guide'),
       (15,14,'4.01 - WSTG-INFO-01 (Conduct Search Engine Discovery and Reconnaissance for Information Leakage)',''),
       (16,14,'4.01 - WSTG-INFO-02 (Fingerprint Web Server)',''),
       (17,14,'4.01 - WSTG-INFO-03 (Review Webserver Metafiles for Information Leakage)',''),
       (18,14,'4.01 - WSTG-INFO-04 (Enumerate Applications on Webserver)',''),
       (19,14,'4.01 - WSTG-INFO-05 (Review Webpage Comments and Metadata for Information Leakage)',''),
       (20,14,'4.01 - WSTG-INFO-06 (Identify application entry points)',''),
       (21,14,'4.01 - WSTG-INFO-07 (Map execution paths through application)',''),
       (22,14,'4.01 - WSTG-INFO-08 (Fingerprint Web Application Framework)',''),
       (23,14,'4.01 - WSTG-INFO-09 (Fingerprint Web Application)',''),
       (24,14,'4.01 - WSTG-INFO-10 (Map Application Architecture )',''),
       (25,14,'4.02 - WSTG-CONF-01 (Test Network/Infrastructure Configuration)',''),
       (26,14,'4.02 - WSTG-CONF-02 (Test Application Platform Configuration)',''),
       (27,14,'4.02 - WSTG-CONF-03 (Test File Extensions Handling for Sensitive Information)',''),
       (28,14,'4.02 - WSTG-CONF-04 (Test Backup and Unreferenced Files for Sensitive Information)',''),
       (29,14,'4.02 - WSTG-CONF-05 (Enumerate Infrastructure and Application Admin Interfaces)',''),
       (30,14,'4.02 - WSTG-CONF-06 (Test HTTP Methods)',''),
       (31,14,'4.02 - WSTG-CONF-07 (Test HTTP Strict Transport Security)',''),
       (32,14,'4.02 - WSTG-CONF-08 (Test RIA cross domain policy)',''),
       (33,14,'4.02 - WSTG-CONF-09 (Test File Permission)',''),
       (34,14,'4.02 - WSTG-CONF-10 (Test for Subdomain Takeover)',''),
       (35,14,'4.02 - WSTG-CONF-11 (Test Cloud Storage)',''),
       (36,14,'4.03 - WSTG-IDNT-01 (Test Role Definitions)',''),
       (37,14,'4.03 - WSTG-IDNT-02 (Test User Registration Process)',''),
       (38,14,'4.03 - WSTG-IDNT-03 (Test Account Provisioning Process)',''),
       (39,14,'4.03 - WSTG-IDNT-04 (Testing for Account Enumeration and Guessable User Account)',''),
       (40,14,'4.03 - WSTG-IDNT-05 (Testing for Weak or unenforced username policy)',''),
       (41,14,'4.04 - WSTG-ATHN-01 (Testing for Credentials Transported over an Encrypted Channel)',''),
       (42,14,'4.04 - WSTG-ATHN-02 (Testing for default credentials)',''),
       (43,14,'4.04 - WSTG-ATHN-03 (Testing for Weak lock out mechanism)',''),
       (44,14,'4.04 - WSTG-ATHN-04 (Testing for bypassing authentication schema)',''),
       (45,14,'4.04 - WSTG-ATHN-05 (Testing for Vulnerable remember password)',''),
       (46,14,'4.04 - WSTG-ATHN-06 (Testing for Browser cache weakness)',''),
       (47,14,'4.04 - WSTG-ATHN-07 (Testing for Weak password policy)',''),
       (48,14,'4.04 - WSTG-ATHN-08 (Testing for Weak security question/answer)',''),
       (49,14,'4.04 - WSTG-ATHN-09 (Testing for weak password change or reset functionalities)',''),
       (50,14,'4.04 - WSTG-ATHN-10 (Testing for Weaker authentication in alternative channel)',''),
       (51,14,'4.05 - WSTG-ATHZ-01 (Testing Directory traversal/file include)',''),
       (52,14,'4.05 - WSTG-ATHZ-02 (Testing for bypassing authorization schema)',''),
       (53,14,'4.05 - WSTG-ATHZ-03 (Testing for Privilege Escalation)',''),
       (54,14,'4.05 - WSTG-ATHZ-04 (Testing for Insecure Direct Object References)',''),
       (55,14,'4.06 - WSTG-SESS-01 (Testing for Bypassing Session Management Schema)',''),
       (56,14,'4.06 - WSTG-SESS-02 (Testing for Cookies attributes)',''),
       (57,14,'4.06 - WSTG-SESS-03 (Testing for Session Fixation)',''),
       (58,14,'4.06 - WSTG-SESS-04 (Testing for Exposed Session Variables)',''),
       (59,14,'4.06 - WSTG-SESS-05 (Testing for Cross Site Request Forgery)',''),
       (60,14,'4.06 - WSTG-SESS-06 (Testing for logout functionality)',''),
       (61,14,'4.06 - WSTG-SESS-07 (Testing Session Timeout)',''),
       (62,14,'4.06 - WSTG-SESS-08 (Testing for Session puzzling)',''),
       (63,14,'4.07 - WSTG-INPV-01 (Testing for Reflected Cross Site Scripting)',''),
       (64,14,'4.07 - WSTG-INPV-02 (Testing for Stored Cross Site Scripting)',''),
       (65,14,'4.07 - WSTG-INPV-03 (Testing for HTTP Verb Tampering)',''),
       (66,14,'4.07 - WSTG-INPV-04 (Testing for HTTP Parameter pollution)',''),
       (67,14,'4.07 - WSTG-INPV-05 (Testing for SQL Injection)',''),
       (68,14,'4.07 - WSTG-INPV-06 (Testing for LDAP Injection)',''),
       (69,14,'4.07 - WSTG-INPV-07 (Testing for XML Injection)',''),
       (70,14,'4.07 - WSTG-INPV-08 (Testing for SSI Injection)',''),
       (71,14,'4.07 - WSTG-INPV-09 (Testing for XPath Injection)',''),
       (72,14,'4.07 - WSTG-INPV-10 (Testing for IMAP SMTP Injection)',''),
       (73,14,'4.07 - WSTG-INPV-11 (Testing for Code Injection)',''),
       (74,14,'4.07 - WSTG-INPV-12 (Testing for Command Injection)',''),
       (75,14,'4.07 - WSTG-INPV-13 (Testing for Buffer overflow)',''),
       (76,14,'4.07 - WSTG-INPV-14 (Testing for incubated vulnerabilities)',''),
       (77,14,'4.07 - WSTG-INPV-15 (Testing for HTTP Splitting/Smuggling)',''),
       (78,14,'4.07 - WSTG-INPV-16 (Testing for HTTP Incoming Requests)',''),
       (79,14,'4.07 - WSTG-INPV-17 (Testing for Host Header Injection)',''),
       (80,14,'4.07 - WSTG-INPV-18 (Testing for Server Side Template Injection)',''),
       (81,14,'4.08 - WSTG-ERRH-01 (Testing for Improper Error Handling)',''),
       (82,14,'4.08 - WSTG-ERRH-02 (Testing for Stack Traces)',''),
       (83,14,'4.09 - WSTG-CRYP-01 (Testing for Weak SSL/TSL Ciphers, Insufficient Transport Layer Protection)',''),
       (84,14,'4.09 - WSTG-CRYP-02 (Testing for Padding Oracle)',''),
       (85,14,'4.09 - WSTG-CRYP-03 (Testing for Sensitive information sent via unencrypted channels)',''),
       (86,14,'4.09 - WSTG-CRYP-04 (Testing for Weak Encryption)',''),
       (87,14,'4.10 - WSTG-BUSL-02 (Test Ability to Forge Requests)',''),
       (88,14,'4.10 - WSTG-BUSL-01 (Test Business Logic Data Validation)',''),
       (89,14,'4.10 - WSTG-BUSL-03 (Test Integrity Checks)',''),
       (90,14,'4.10 - WSTG-BUSL-04 (Test for Process Timing)',''),
       (91,14,'4.10 - WSTG-BUSL-05 (Test Number of Times a Function Can be Used Limits)',''),
       (92,14,'4.10 - WSTG-BUSL-06 (Testing for the Circumvention of Work Flows)',''),
       (93,14,'4.10 - WSTG-BUSL-07 (Test Defenses Against Application Mis-use)',''),
       (94,14,'4.10 - WSTG-BUSL-08 (Test Upload of Unexpected File Types)',''),
       (95,14,'4.10 - WSTG-BUSL-09 (Test Upload of Malicious Files)',''),
       (96,14,'4.11 - WSTG-CLNT-01 (Testing for DOM based Cross Site Scripting)',''),
       (97,14,'4.11 - WSTG-CLNT-02 (Testing for JavaScript Execution)',''),
       (98,14,'4.11 - WSTG-CLNT-03 (Testing for HTML Injection)',''),
       (99,14,'4.11 - WSTG-CLNT-04 (Testing for Client Side URL Redirect)',''),
       (100,14,'4.11 - WSTG-CLNT-05 (Testing for CSS Injection)',''),
       (101,14,'4.11 - WSTG-CLNT-06 (Testing for Client Side Resource Manipulation)',''),
       (102,14,'4.11 - WSTG-CLNT-07 (Testing Cross Origin Resource Sharing)',''),
       (103,14,'4.11 - WSTG-CLNT-08 (Testing for Cross Site Flashing)',''),
       (104,14,'4.11 - WSTG-CLNT-09 (Testing for Clickjacking)',''),
       (105,14,'4.11 - WSTG-CLNT-10 (Testing WebSockets)',''),
       (106,14,'4.11 - WSTG-CLNT-11 (Testing Web Messaging)',''),
       (107,14,'4.11 - WSTG-CLNT-12 (Testing Browser Storage)',''),
       (108,14,'4.11 - WSTG-CLNT-13 (Testing for Cross Site Script Inclusion)','');

TRUNCATE TABLE contact;
INSERT INTO contact (name, email)
VALUES ('Contributors', 'no-reply@reconmap.com');

TRUNCATE TABLE organisation;
INSERT INTO organisation (name, url, contact_id)
VALUES ('Reconmap organisation', 'https://reconmap.com', LAST_INSERT_ID());

INSERT INTO report (project_id, generated_by_uid, is_template, version_name, version_description)
VALUES (0, 0, 1, 'Default', 'Default report template');

INSERT INTO attachment (parent_type, parent_id, submitter_uid, client_file_name, file_name, file_size, file_mimetype,
                        file_hash)
VALUES ('report', LAST_INSERT_ID(), 0, 'default-report-template.docx', 'default-report-template.docx', 0,
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '');
