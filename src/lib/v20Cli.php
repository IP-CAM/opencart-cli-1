<?php

/**
 * Class v20Cli
 *
 * @author Istvan Dobrentei
 * @link https://www.dobrenteiistvan.hu
 */
class v20Cli extends BaseCli
{
    /**
     * v20Cli constructor.
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

        require_once $pathToRoot . '/system/startup.php';
        
        // Engine
        require_once($this->modification(DIR_SYSTEM . 'engine/action.php'));
        require_once($this->modification(DIR_SYSTEM . 'engine/controller.php'));
        require_once($this->modification(DIR_SYSTEM . 'engine/event.php'));
        require_once($this->modification(DIR_SYSTEM . 'engine/loader.php'));
        require_once($this->modification(DIR_SYSTEM . 'engine/model.php'));
        require_once($this->modification(DIR_SYSTEM . 'engine/registry.php'));
        require_once($this->modification(DIR_SYSTEM . 'engine/proxy.php'));

        // Helper
        require_once(DIR_SYSTEM . 'helper/general.php');
        require_once(DIR_SYSTEM . 'helper/utf8.php');
        require_once(DIR_SYSTEM . 'helper/json.php');

        ############# FRAMEWORK ################## -->

        // Registry
        $this->_registry = new Registry();

        // Config
        $config = new Config();
        $config->load('default');
        $config->load($context);
        $this->_registry->set('config', $config);

        // Event
        $event = new Event($this->_registry);
        $this->_registry->set('event', $event);

        // Event Register
        if ($config->has('action_event')) {
            foreach ($config->get('action_event') as $key => $value) {
                $event->register($key, new Action($value));
            }
        }

        // Loader
        $loader = new Loader($this->_registry);
        $this->_registry->set('load', $loader);

        // Request
        $this->_registry->set('request', new Request());

        // Response
        $response = new Response();
        $response->addHeader('Content-Type: text/html; charset=utf-8');
        $this->_registry->set('response', $response);

        // Database
        if ($config->get('db_autostart')) {
            $this->_registry->set('db', new DB($config->get('db_type'), $config->get('db_hostname'), $config->get('db_username'), $config->get('db_password'), $config->get('db_database'), $config->get('db_port')));
        }

        // Session
        $session = new Session();

        if ($config->get('session_autostart')) {
            $session->start();
        }

        $this->_registry->set('session', $session);

        // Cache
        $this->_registry->set('cache', new Cache($config->get('cache_type'), $config->get('cache_expire')));

        // Url
        if ($config->get('url_autostart')) {
            $this->_registry->set('url', new Url($config->get('site_base'), $config->get('site_ssl')));
        }

        // Language
        $language = new Language($config->get('language_default'));
        $language->load($config->get('language_default'));
        $this->_registry->set('language', $language);

        // Document
        $this->_registry->set('document', new Document());

        // Config Autoload
        if ($config->has('config_autoload')) {
            foreach ($config->get('config_autoload') as $value) {
                $loader->config($value);
            }
        }

        // Language Autoload
        if ($config->has('language_autoload')) {
            foreach ($config->get('language_autoload') as $value) {
                $loader->language($value);
            }
        }

        // Library Autoload
        if ($config->has('library_autoload')) {
            foreach ($config->get('library_autoload') as $value) {
                $loader->library($value);
            }
        }

        // Model Autoload
        if ($config->has('model_autoload')) {
            foreach ($config->get('model_autoload') as $value) {
                $loader->model($value);
            }
        }



        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '0'");

        foreach ($query->rows as $setting) {
            if (!$setting['serialized']) {
                $this->config->set($setting['key'], $setting['value']);
            } else {
                $this->config->set($setting['key'], json_decode($setting['value'], true));
            }
        }

        // Language
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE code = '" . $this->db->escape($this->config->get('config_admin_language')) . "'");

        if ($query->num_rows) {
            $this->config->set('config_language_id', $query->row['language_id']);
        }

        // Language
        $language = new Language($this->config->get('config_admin_language'));
        $language->load($this->config->get('config_admin_language'));
        $this->_registry->set('language', $language);

        // Customer
        $this->_registry->set('customer', new Cart\Customer($this->_registry));

        // Affiliate
        $this->_registry->set('affiliate', new Cart\Affiliate($this->_registry));

        // Currency
        $this->_registry->set('currency', new Cart\Currency($this->_registry));

        // Tax
        $this->_registry->set('tax', new Cart\Tax($this->_registry));

        if ($this->config->get('config_tax_default') == 'shipping') {
            $this->tax->setShippingAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));
        }

        if ($this->config->get('config_tax_default') == 'payment') {
            $this->tax->setPaymentAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));
        }

        $this->tax->setStoreAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));

        // Weight
        $this->_registry->set('weight', new Cart\Weight($this->_registry));

        // Length
        $this->_registry->set('length', new Cart\Length($this->_registry));

        // Cart
        $this->_registry->set('cart', new Cart\Cart($this->_registry));

        // Encryption
        $this->_registry->set('encryption', new Encryption($this->config->get('config_encryption')));

        // OpenBay Pro
        $this->_registry->set('openbay', new Openbay($this->_registry));

    }
}