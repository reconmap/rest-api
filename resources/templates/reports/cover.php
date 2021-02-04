<?php
/** @global \Reconmap\Models\Client $client */
/** @global \Reconmap\Models\Organisation $org */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <meta name="author" content="<?= $org->name ?>"/>
    <style type="text/css">
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
</body>
</html>
