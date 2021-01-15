<?php
/** @global \Reconmap\Models\Client $client */
/** @global \Reconmap\Models\Organisation $org */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <meta name="author" content="<?= $org->name ?>"/>
</head>

<body>
<div style="text-align: center;">
    <h2>Security report</h2>

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
        <dl>
            <dt>Category</dt>
            <dd><?= $vuln['category_name'] ?></dd>

            <dt>CVSS score</dt>
            <dd><?= $vuln['cvss_score'] ?></dd>

            <dt>CVSS vector</dt>
            <dd><?= $vuln['cvss_vector'] ?></dd>
        </dl>
        <p><?= $vuln['description'] ?></p>
        <div style="break-after:page"></div>
    <?php endforeach ?>
</div>

</body>
</html>
