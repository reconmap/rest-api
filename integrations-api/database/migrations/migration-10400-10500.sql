SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE project_category
(
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name        VARCHAR(200)  NOT NULL,
    description VARCHAR(2000) NULL,

    PRIMARY KEY (id),
    UNIQUE KEY (name)
) ENGINE = InnoDB
  CHARSET = utf8mb4;

INSERT INTO project_category (name, description)
VALUES ('Managed security monitoring',
        'Includes the day-to-day monitoring and investigation of system events throughout the network as well as security events, such as user permission changes and user logins.'),
       ('Vulnerability risk assessment',
        'Determines the state of the organization''s existing security readiness, and provides insights into potential vulnerabilities for minimizing exposure.'),
       ('Compliance monitoring',
        'Involves checking how well the organization complies with data security policies and procedures. The MSSP typically performs ongoing scans of security devices and infrastructure to determine if any changes need to be made to boost compliance. And with the compliance landscape becoming more complex all the time, this service is especially valuable to organizations that need to comply with GDPR, CCPA, HIPAA, PCI DSS, and others.'),
       ('Threat intelligence',
        'Involves gathering information to help the organization determine which threats have, will, or are currently targeting the organization and its employees, as well as which of these threats represent a viable risk.'),
       ('Security consultation',
        'for several domains including executing a detailed assessment of the network to identify potential and real-world vulnerabilities, finding security lacunae, and providing recommendations on how to fix them.'),
       ('Security program development',
        'Includes policy development for helping to protect the organization’s infrastructure, systems, network, and devices.'),
       ('Perimeter management',
        'Protects the defenses around the network from external attackers as well as from bad insiders.Relevant activities including establishing the controls and processes that limit access to sensitive data in the network and on the end point.'),
       ('Penetration testing',
        'Also known as pentesting, which entails simulating a cyberattack against the organization’s information and technology assets to check for exploitable vulnerabilities. This service constitutes a form of ethical hacking that can be very effective at uncovering the vulnerabilities that may be successfully targeted by hackers ');

ALTER TABLE project
    DROP COLUMN engagement_type,
    ADD COLUMN category_id INT UNSIGNED NULL AFTER client_id,
    ADD CONSTRAINT project_fk_category_id FOREIGN KEY (category_id) REFERENCES project_category (id) ON DELETE SET NULL;

UPDATE project
SET category_id = (SELECT id FROM project_category WHERE name = 'Penetration testing');

SET FOREIGN_KEY_CHECKS = 1;
