<?php
spl_autoload_register(function($className) {
    $currentDir = dirname(__FILE__);
    $namespace = str_replace("\\", "/", __NAMESPACE__);
    $className = str_replace("\\", "/" ,$className);
    $class = (empty($namespace) ? $currentDir . "/lib/" : $namespace . "/") . "{$className}.php";
    if(file_exists($class))
    {
        include_once($class);
    }
});
$params = getopt(null, OpencartCli::getParams());
$cli = new OpencartCli($params);