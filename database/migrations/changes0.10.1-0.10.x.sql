ALTER TABLE project
    ADD COLUMN vulnerability_metrics ENUM ('CVSS', 'OWASP_RR') NULL;
ALTER TABLE vulnerability 
    ADD COLUMN owasp_vector VARCHAR(80) NULL,
    ADD COLUMN owasp_likehood DECIMAL(3, 1) NULL,
    ADD COLUMN owasp_impact DECIMAL(3, 1) NULL,
    ADD COLUMN owasp_overall DECIMAL(3, 1) NULL;
