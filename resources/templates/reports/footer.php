<!doctype html>
<html>
<head>
    <meta charset="utf-8"/>
    <style type="text/css">
        html, body {
            margin: 0;
            padding: 0;
        }

        div {
            padding: 5px;
            text-align: center;
            border-top: 2px solid #D44C46;
            font-size: 10px;
            background-color: #1B202B;
            color: white;
        }
    </style>
    <script>
        function substitutePdfVariables() {

            function getParameterByName(name) {
                var match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
                return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
            }

            function substitute(name) {
                var value = getParameterByName(name);
                var elements = document.getElementsByClassName(name);

                for (var i = 0; elements && i < elements.length; i++) {
                    elements[i].textContent = value;
                }
            }

            ['frompage', 'topage', 'page', 'webpage', 'section', 'subsection', 'subsubsection']
                .forEach(function (param) {
                    substitute(param);
                });
        }
    </script>
</head>
<body onload="substitutePdfVariables()">
<div>Page <span class="page"></span> of <span class="topage"></span></div>
</body>
</html>
