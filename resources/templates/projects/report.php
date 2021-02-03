<?php
/** @global \Reconmap\Models\Client $client */
/** @global \Reconmap\Models\Organisation $org */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <meta name="author" content="<?= $org->name ?>"/>
    <style>
        @page :first {
            margin-top: 50px;
        }

        @page {
            margin-top: 100px;
            margin-bottom: 100px;
        }

        @page :left {
            @bottom-left {
                content: "Page " counter(page) " of " counter(pages);
            }
        }

        @media print {

            a {
                color: #748094;
            }

            body {
                margin-top: 1cm;
            }

            * {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            }

            .header {
                background-color: red;
                -webkit-print-color-adjust: exact;
                height: 100px;
                width: 100%;
                text-align: left;
                font-size: 12px;
            }

            table {
                width: 80%;
                border-collapse: collapse;
            }

            tr td:first-child {
                font-weight: bold;
                width: 300px;
            }

            td {
                padding: 5px;
                border: 1px solid black;
            }

            td.risk-low {
                background-color: green;
                color: white;
            }

            td.risk-medium {
                background-color: yellow;
                color: black;
            }

            td.risk-high {
                background-color: orange;
                color: white;
            }

            td.risk-critical {
                background-color: darkred;
                color: white;
            }


            .new-page-separator {
                page-break-before: always;
            }


        }

    </style>
</head>

<body>
<div style="text-align: center;">
    <h2>Security report</h2>

    <img
        src="https://camo.githubusercontent.com/ec1ac125f3b29483c6610c7aa548af7acf17f141029db31a7cbc2d32eb2e9d50/68747470733a2f2f7061737465616c6c2e6f72672f6d656469612f342f372f34373830633330373233663930636664353665633064303536353535623765362e706e67"/>
    <dl>
        <dt>Version</dt>
        <dd><?= $version ?></dd>

        <dt>Date</dt>
        <dd><?= $date ?></dd>
    </dl>
    <p><em>Prepared for <a href="<?= $client->url ?>"><?= $client->name ?></a>.</em></p>
    <p><em>Prepared by <a href="<?= $org->url ?>"><?= $org->name ?></a>.</em></p>

    <h3>CONTENT IS CONFIDENTIAL</h3>

</div>

<div style="break-after:page"></div>

<h3><?= $project['name'] ?></h3>
<p><?= $project['description'] ?></p>

<h2>Version control</h2>

<table>
    <thead>
    <tr>
        <th>Date/Time</th>
        <th>Version</th>
        <th>Description</th>
    </tr>
    </thead>
    <tbody>
    <?php /** @global array $versions */ ?>
    <?php foreach ($reports as $version): ?>
        <tr>
            <td><?= $version['insert_ts'] ?></td>
            <td><?= $version['version_name'] ?></td>
            <td><?= $version['version_description'] ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>

<h2>Table of contents</h2>

<ol>
    <li><a href="#pentesting-team">Pentesting team</a></li>
    <li><a href="#targets">Targets</a></li>
    <li><a href="#findings-overview">Findings overview</a></li>
</ol>

<div style="break-after:page"></div>
<h2><a name="pentesting-team">Pentesting team</a></h2>

<ul>

    <?php /** @var array $users */
    foreach ($users as $user) : ?>
        <li><strong><?= $user['full_name'] ?></strong> <?= $user['short_bio'] ?></li>
    <?php endforeach ?>
</ul>

<div style="break-after:page"></div>
<h2><a name="targets">Targets</a></h2>

<ul>
    <?php
    /** @var array $targets */
    foreach ($targets as $target) : ?>
        <li><strong><?= $target['name'] ?></strong> (<?= $target['kind'] ?>)</li>
    <?php endforeach ?>
</ul>

<div style="break-after:page"></div>
<h2><a name="findings-overview">Findings overview</a></h2>

<table>
    <thead>
    <tr>
        <th>Severity</th>
        <th>Count</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($findingsOverview as $findingOverview): ?>
        <tr>
            <td><?= $findingOverview['severity'] ?></td>
            <td><?= $findingOverview['count'] ?></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>

<div style="break-after:page"></div>
<h2>Vulnerabilities</h2>

<div>
    <?php foreach ($vulnerabilities as $vuln) : ?>
        <h3><?= $vuln['summary'] ?></h3>

        <table>
            <tbody>
            <tr>
                <td>Severity</td>
                <td class="risk-<?= $vuln['risk'] ?>"><?= ucfirst($vuln['risk']) ?></td>
            </tr>
            <tr>
                <td>Category</td>
                <td><?= $vuln['category_name'] ?></td>
            </tr>
            <tr>
                <td>CVSS score</td>
                <td>
                    <a href="https://www.first.org/cvss/calculator/3.0#CVSS:3.0/AV:P/AC:H/PR:H/UI:R/S:C/C:H/I:H/A:H"><?= $vuln['cvss_score'] ?></a>
                </td>
            </tr>
            </tbody>
        </table>
        <p><?= $markdownParser->text($vuln['description']) ?></p>
    <?php endforeach ?>
</div>

</body>
</html>
