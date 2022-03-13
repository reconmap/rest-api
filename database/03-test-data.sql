SET @admin_user_id = 1;

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
       (1, 'Nmap', 'Scans all reserved TCP ports on the machine', 'instrumentisto/nmap',
        '-v {{{Host|||scanme.nmap.org}}} -oX nmap-output.xml', 'rmap', 'nmap-output.xml', NULL, '[
         "network"
       ]', 'nmap')
        ,
       (1, 'Whois', 'Retrieves information about domain', 'zeitgeist/docker-whois', '{{{Domain|||nmap.org}}}', 'rmap',
        NULL, NULL, '[
         "domain"
       ]', NULL)
        ,
       (1, 'SQLmap', 'Runs SQL map scan', 'paoloo/sqlmap',
        '-u {{{Host|||localhost}}} --method POST --data "{{{Data|||username=foo&password=bar}}}" -p username --level 5 --dbms=mysql -v 1 --tables',
        'rmap', NULL, NULL, '[
         "sql",
         "database"
       ]', 'sqlmap');

