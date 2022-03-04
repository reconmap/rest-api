SET @admin_user_id = 1;

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
