<?php
//run as php -dphar.readonly=0 create-phar.php
$pharFile = 'opencart-cli.phar';

if(file_exists($pharFile)){
    unlink($pharFile);
}
if(file_exists($pharFile . '.gz')){
    unlink($pharFile . '.gz');
}
$p = new Phar($pharFile);
$p->buildFromDirectory('src/');
$p->setDefaultStub('index.php', '/index.php');
$p->compress(Phar::GZ);
if(file_exists($pharFile . '.gz')){
    unlink($pharFile . '.gz');
}
echo "$pharFile successfully created" . PHP_EOL;