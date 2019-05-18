<?php

/**
 * Class v15Cli
 *
 * @author Istvan Dobrentei
 * @link https://www.dobrenteiistvan.hu
 */
class v15Cli extends BaseCli
{
    /**
     * v15Cli constructor.
     *
     * @param $pathToRoot
     * @param string $context
     * @param int $defaultStoreId
     * @param int $defaultLanguageId
     */
    public function __construct(
        $pathToRoot,
        $context = OpencartCli::CONTEXT_CATALOG,
        $defaultStoreId = 0,
        $defaultLanguageId = 0
    ) {
        parent::__construct($pathToRoot, $context);

        //
        require_once $pathToRoot . '/system/engine/registry.php';
        require_once $pathToRoot . '/system/engine/loader.php';
        require_once $pathToRoot . '/system/engine/controller.php';
        require_once $pathToRoot . '/system/engine/model.php';
        //
        require_once $pathToRoot . '/system/library/config.php';
        require_once $pathToRoot . '/system/library/db.php';
        require_once $pathToRoot . '/system/library/language.php';
        require_once $pathToRoot . '/system/library/cache.php';
        require_once $pathToRoot . '/system/library/length.php';
        require_once $pathToRoot . '/system/library/log.php';
        require_once $pathToRoot . '/system/helper/utf8.php';

        $this->_registry = new Registry();
        $loader = new Loader($this->_registry);

        $this->_registry->set('load', $loader);
        $db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
        $this->_registry->set('db', $db);

        $language = new Language('english');
        $language->load('english');
        $this->_registry->set('language', $language);

        $config = new Config();
        $config->set('config_store_id', $defaultStoreId);
        $query = $db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id='" . (int)$defaultStoreId . "' OR
                             store_id='" . (int)$config->get('config_store_id') . "' ORDER BY store_id ASC");

        foreach($query->rows as $setting)
        {
            if(!$setting['serialized'])
            {
                $config->set($setting['key'], $setting['value']);
            }
            else
            {
                $config->set($setting['key'], unserialize($setting['value']));
            }
        }

        $config->set('config_language_id', $defaultLanguageId);
        $this->_registry->set('config', $config);

        $log = new Log($config->get('config_error_filename'));
        $this->_registry->set('log', $log);

        $cache = new Cache();
        $this->_registry->set('cache', $cache);

        $length = new Length($this->_registry);
        $this->_registry->set('length', $length);

        $this->_log = $this->_registry->get('log');
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_registry->get($name);
    }
}