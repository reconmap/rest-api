<html>

<head>
    <meta name="author" content="Reconmap" />
</head>

<body>
    <center>
    <h2>Security report</h2>
    <p><em>Generated on the <?= $date ?> by <a href="https://reconmap.org">Reconmap</a>.</em></p>

    <h3>CONTENT IS CONFIDENTIAL</h3>

    </center>

    <div style="break-after:page"></div>

    <h3><?= $project['name'] ?></h3>
    <p><?= $project['description'] ?></p>

    <div style="break-after:page"></div>
    <h2>Targets</h2>

    <ul>
        <?php foreach ($targets as $target) : ?>
            <li><strong><?= $target['name'] ?></strong></li>
        <?php endforeach ?>
    </ul>

    <div style="break-after:page"></div>
    <h2>Vulnerabilities</h2>

    <ul>
        <?php foreach ($vulnerabilities as $vuln) : ?>
            <h3><?= $vuln['summary'] ?></h3>
            <p><?= $vuln['description'] ?></p>
        <?php endforeach ?>
    </ul>

</body>

</html>