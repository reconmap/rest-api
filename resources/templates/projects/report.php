<h2>Security report</h2>
<h3>Generated on the <?= $date ?> by <a href="https://reconmap.org">Reconmap</a>.</h3>

<p>CONTENT IS CONFIDENTIAL</p>
<div style="break-after:page"></div>
<h3><?= $project['name'] ?></h3>
<p><?= $project['description'] ?></p>

<div style="break-after:page"></div>
<h2>Vulnerabilities</h2>

<ul>
    <?php foreach ($vulnerabilities as $vuln) : ?>
        <li><strong><?= $vuln['summary'] ?></strong></li>
    <?php endforeach ?>
</ul>