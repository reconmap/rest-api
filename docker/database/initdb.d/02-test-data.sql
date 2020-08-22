
TRUNCATE TABLE user;
INSERT INTO user (id, name, password, email, role) VALUES (1, 'admin', 'admin123', 'admin@localhost', 'creator');

TRUNCATE TABLE audit_log;
INSERT INTO audit_log (user_id, client_ip, action) VALUES (1, INET_ATON('127.0.0.1'), 'Initialised system');

INSERT INTO project (id, name, description, is_template) VALUES
    (1, 'Linux host template', 'Project template to show general linux host reconnaissance tasks', TRUE),
    (2, 'Web server pentest project', 'Test project to show pentest tasks and reports', FALSE),
    (3, 'Linux host', 'Test project to show general linux host reconnaissance tasks', FALSE);

INSERT INTO target (project_id, name, kind) VALUES
    (1, 'test.com', 'webapp'),
    (2, '127.0.0.1', 'host');

INSERT INTO vulnerability (project_id, target_id, reported_by_uid, summary, risk) VALUES
    (1, 1, 1, 'Domain about to expire', 'medium'),
    (1, 2, 1, 'Open port (tcp/22)', 'medium');

INSERT INTO task (project_id, parser, name, description) VALUES
    (1, 'nmap', 'Run port scanner', 'nmap -oX out.xml -v -sS @@TARGET@@'),
    (1, 'sqlmap', 'Run SQL injection scanner', 'python sqlmap.py -u @@TARGET@@ --method POST --data "username=foo&password=bar" -p username --level 5 --dbms=mysql -v 1 --tables'),
    (1, NULL, 'Check domain expiration date', 'whois @@TARGET@@'),
    (3, 'nmap', 'Run port scanner', 'nmap -oX out.xml -v -sS @@TARGET@@'),
    (3, 'sqlmap', 'Run SQL injection scanner', 'python sqlmap.py -u @@TARGET@@ --method POST --data "username=foo&password=bar" -p username --level 5 --dbms=mysql -v 1 --tables'),
    (3, NULL, 'Check domain expiration date', 'whois @@TARGET@@');

INSERT INTO task_result (task_id, submitted_by_uid, output) VALUES
    (1, 1, 'tcp/22: open, tcp/80: open'),
    (1, 2, 'Domain expires in 22 days');
