SET @admin_user_id = 1;

UPDATE user
SET full_name = 'Jane Doe',
    short_bio = 'CEO and CTO of Amazing Pentest Company Limited'
WHERE id = @admin_user_id;

INSERT INTO user (id, full_name, username, password, email, role)
VALUES (2,
        'Lead pentester',
        'su',
        '$2y$10$7u3qUhud4prBZdFVmODvXOCBuQBgq6MYHvZT7N74cMG/mnVBwiu7W',
        'su@localhost',
        'superuser'),
       (3,
        'Infosec pro',
        'user',
        '$2y$10$pTgvYwR3Umwvb.cpIWw5kOpoqj49q.Q9tzHcRXcAnXdUaQe5C.Nom',
        'user@localhost',
        'user'),
       (4,
        'Dear Customer',
        'cust',
        '$2y$10$/VVITsgw9ByDoCTCKTuBtemc44SoP4691aIVVyd/OgLblXQK6Tnwq',
        'cust@localhost',
        'client');

INSERT INTO audit_log (user_id, client_ip, action)
VALUES (1, INET_ATON('127.0.0.1'), 'Logged in'),
       (1, INET_ATON('127.0.0.1'), 'Logged in'),
       (1, INET_ATON('127.0.0.1'), 'Logged in'),
       (1, INET_ATON('127.0.0.1'), 'Logged in'),
       (1, INET_ATON('127.0.0.1'), 'Logged in');

INSERT INTO client (id, creator_uid, name, url, contact_name, contact_email, contact_phone)
VALUES (1,
        @admin_user_id,
        'Insecure Co.',
        'http://in.se.cure',
        'John Doe',
        'John.Doe@in.se.cure',
        '+99 123 245 389'),
       (2,
        @admin_user_id,
        'The OWASP Foundation',
        'https://owasp.org',
        'N/A',
        'N/A',
        '+1 951-692-7703');

INSERT INTO project (id, creator_uid, client_id, name, description, is_template, visibility, external_id)
VALUES (1,
        @admin_user_id,
        NULL,
        'Linux host template',
        'Project template to show general linux host reconnaissance tasks',
        TRUE,
        'public',
        NULL),
       (2,
        @admin_user_id,
        1,
        'Web server pentest project',
        'Test project to show pentest tasks and reports',
        FALSE,
        'private',
        'C8D6355A-5F54-43FC-A947-C4C960CDD4F6'),
       (3,
        @admin_user_id,
        2,
        'Juice Shop (test project)',
        'OWASP Juice Shop is probably the most modern and sophisticated insecure web application! It can be used in security trainings,
awareness demos,
CTFs and as a guinea pig for security tools! Juice Shop encompasses vulnerabilities from the entire OWASP Top Ten along with many other security flaws found in real -world applications!',
        FALSE,
        'public'),
       (4,
        @admin_user_id,
        2,
        ' WebGoat (test project)',
        ' WebGoat is a deliberately insecure application that allows interested developers just like you to test vulnerabilities commonly found in Java-based applications that use common and popular open source components.',
        FALSE,
        'private',
        NULL);

INSERT INTO report (project_id,
                    insert_ts,
                    generated_by_uid,
                    version_name,
                    version_description)
VALUES (2,
        CURRENT_TIMESTAMP,
        1,
        '1.0',
        ' Initial version '),
       (2,
        DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 3 DAY),
        1,
        '1.1',
        ' Initial version after corrections '),
       (2,
        DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 10 DAY),
        1,
        '1.2 reviewed ',
        ' Report reviewed and sent to the client ');

INSERT INTO project_user (project_id, user_id)
VALUES (2, 1),
       (2, 2);

INSERT INTO target (project_id, name, kind, tags)
VALUES (1, ' https://test.com ', 'url', NULL),
       (2, '127.0.0.1', 'hostname', '[
         "linux",
         "dev-environment"
       ]');

INSERT INTO command (creator_uid, name, description, docker_image, arguments, executable_type, output_filename,
                     more_info_url, tags, output_parser)
VALUES (1, 'Goohost',
        'Extracts hosts/subdomains, IP or emails for a specific domain with Google search.',
        'reconmap/pentest-container-tools-goohost',
        '-t {{{Domain|||nmap.org}}}', 'rmap', NULL, NULL, '[
    "google",
    "domain"
  ]', NULL)
        ,
       (2, 'Nmap', 'Scans all reserved TCP ports on the machine', 'instrumentisto/nmap',
        '-v {{{Host|||scanme.nmap.org}}} -oX nmap-output.xml', 'rmap', 'nmap-output.xml', NULL, '[
         "network"
       ]', 'nmap')
        ,
       (3, 'Whois', 'Retrieves information about domain', 'zeitgeist/docker-whois', '{{{Domain|||nmap.org}}}', 'rmap',
        NULL, NULL, '[
         "domain"
       ]', NULL)
        ,
       (4, 'SQLmap', 'Runs SQL map scan', 'paoloo/sqlmap',
        '-u {{{Host|||localhost}}} --method POST --data "{{{Data|||username=foo&password=bar}}}" -p username --level 5 --dbms=mysql -v 1 --tables',
        'rmap', NULL, NULL, '[
         "sql",
         "database"
       ]', 'sqlmap');

INSERT INTO notification (to_user_id, title, content)
VALUES (@admin_user_id, 'Command completed', '100 vulnerabilities have been found');
