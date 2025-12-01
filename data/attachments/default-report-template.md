# Penetration Test Report

**Project:** {{ project.name }}  
**Prepared for:** {{ client.name }}  
**Prepared by:** {{ serviceProvider.name }}  
**Date:** {{ date }}

---

## Revisions

{% for revision in revisions %}

-   **{{ revision.created_at }}**  
     _{{ revision.version_name }}_ — {{ revision.version_description }}
    {% endfor %}

---

## Contacts

{% for contact in client.contacts %}

-   **Name:** {{ contact.name }}  
    **Phone:** {{ contact.phone }}  
    **Email:** {{ contact.email }}  
    **Role:** {{ contact.role }}

{% endfor %}

---

## Pentesting Team

{% for user in users %}

-   **{{ user.full_name }}**  
     _{{ user.short_bio }}_
    {% endfor %}

---

## Project Overview

{{ project.description }}

{% for finding in findings.stats %}

-   **{{ finding.severity|capitalize }}:** {{ finding.count }}
    {% endfor %}

---

## Targets

{% for asset in assets %}

-   **{{ asset.name }}** (_{{ asset.kind }}_)
    {% endfor %}

---

## Vulnerabilities

{% for vulnerability in findings.list %}

### {{ vulnerability.summary }}

-   **Category:** {{ vulnerability.category_name }}
-   **Severity:** {{ vulnerability.risk|capitalize }}
-   **CVSS Score:** {{ vulnerability.cvss_score }}
-   **OWASP Vector:** {{ vulnerability.owasp_vector }}
-   **OWASP Overall Rating:** {{ vulnerability.owasp_overall }}

**Description:**  
{{ vulnerability.description }}

**Proof of Concept:**  
{{ vulnerability.proof_of_concept }}

**Remediation:**  
{{ vulnerability.remediation }}

---

{% endfor %}
