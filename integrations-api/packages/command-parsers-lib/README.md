[![codecov](https://codecov.io/gh/reconmap/php-command-output-parsers/branch/master/graph/badge.svg?token=t3ODnO2R8u)](https://codecov.io/gh/reconmap/php-command-output-parsers)

# Reconmap security command output parsers library

## Supported tools

-   Acunetix
-   Burp
-   Metasploit
-   Nessus
-   Nmap
-   Nuclei
-   OpenVAS
-   Qualys
-   SQLmap
-   Subfinder
-   shcheck
-   TestSSL
-   ZAP

## Requirements

-   [PHP8.5](https://www.php.net/releases/8.5/en.php)
-   Composer

## Usage

```shell
composer require reconmap/command-output-parsers
```

## Examples

### Nessus

```php
$processorFactory = new ProcessorFactory();
$processor = $processorFactory->createFromOutputParserName('acunetix');
$result = $processor->process('resources/nessus.xml');

echo $result->getVulnerabilities()[4]->remediation), PHP_EOL; # Prints 'Protect your target with an IP filter.'

foreach($result->getAssets() as $asset) {
    echo $asset->getValue(), PHP_EOL;
}
```
