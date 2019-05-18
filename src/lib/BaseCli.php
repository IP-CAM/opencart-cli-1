<?php

/**
 * Class BaseCli
 *
 * @author Istvan Dobrentei
 * @link https://www.dobrenteiistvan.hu
 */
abstract class BaseCli
{
    protected $_registry;
    protected $_db;
    protected $_log;

    /**
     * BaseCli constructor.
     *
     * @param $pathToRoot
     * @param $context
     */
    public function __construct($pathToRoot, $context)
    {
        if(empty($pathToRoot))
        {
            throw new Exception('Please set the path to the store!');
        }

        $configPath = $context==OpencartCli::CONTEXT_CATALOG ? $pathToRoot . '/config.php' : $pathToRoot . '/admin/config.php';

        if(!file_exists($configPath))
        {
            throw new Exception('Store config does not exist!');
        }

        require_once $configPath;

        empty( $_SERVER['HTTP_HOST'] ) && $_SERVER['HTTP_HOST'] = 'localhost';
        empty( $_SERVER['REQUEST_URI'] ) && $_SERVER['REQUEST_URI'] = '/';
        empty( $_SERVER['DOCUMENT_ROOT'] ) && $_SERVER['DOCUMENT_ROOT'] = __DIR__;
        empty( $_SERVER['SERVER_PORT'] ) && $_SERVER['SERVER_PORT'] = '';
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_registry->get($name);
    }

    // Modification Override
    protected function modification($filename)
    {
        if (defined('DIR_CATALOG'))
        {
            $file = DIR_MODIFICATION . 'admin/' .  substr($filename, strlen(DIR_APPLICATION));
        }
        elseif (defined('DIR_OPENCART'))
        {
            $file = DIR_MODIFICATION . 'install/' .  substr($filename, strlen(DIR_APPLICATION));
        }
        else
        {
            $file = DIR_MODIFICATION . 'catalog/' . substr($filename, strlen(DIR_APPLICATION));
        }

        if (substr($filename, 0, strlen(DIR_SYSTEM)) == DIR_SYSTEM)
        {
            $file = DIR_MODIFICATION . 'system/' . substr($filename, strlen(DIR_SYSTEM));
        }

        if (is_file($file))
        {
            return $file;
        }

        return $filename;
    }
}