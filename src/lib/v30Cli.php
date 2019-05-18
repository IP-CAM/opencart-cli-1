<?php

/**
 * Class v30Cli
 *
 * @author Istvan Dobrentei
 * @link https://www.dobrenteiistvan.hu
 */
class v30Cli extends BaseCli
{
    /**
     * v30Cli constructor.
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

        // Autoloader
        if (is_file(DIR_STORAGE . 'vendor/autoload.php')) {
            require_once(DIR_STORAGE . 'vendor/autoload.php');
        }

        spl_autoload_register('library');
        spl_autoload_extensions('.php');

        // Engine
        require_once($this->modification(DIR_SYSTEM . 'engine/action.php'));
        require_once($this->modification(DIR_SYSTEM . 'engine/controller.php'));
        require_once($this->modification(DIR_SYSTEM . 'engine/event.php'));
        require_once($this->modification(DIR_SYSTEM . 'engine/router.php'));
        require_once($this->modification(DIR_SYSTEM . 'engine/loader.php'));
        require_once($this->modification(DIR_SYSTEM . 'engine/model.php'));
        require_once($this->modification(DIR_SYSTEM . 'engine/registry.php'));
        require_once($this->modification(DIR_SYSTEM . 'engine/proxy.php'));

        // Helper
        require_once(DIR_SYSTEM . 'helper/general.php');
        require_once(DIR_SYSTEM . 'helper/utf8.php');

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
                foreach ($value as $priority => $action) {
                    $event->register($key, new Action($action), $priority);
                }
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

        if ($config->get('db_autostart')) {
            $this->_registry->set('db', new DB($config->get('db_engine'), $config->get('db_hostname'), $config->get('db_username'), $config->get('db_password'), $config->get('db_database'), $config->get('db_port')));
        }

        // Session
        $session = new Session($config->get('session_engine'), $this->_registry);
        $this->_registry->set('session', $session);

        if ($config->get('session_autostart')) {
            $session->start();
        }

        // Cache
        $this->_registry->set('cache', new Cache($config->get('cache_engine'), $config->get('cache_expire')));

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



        // Settings
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE store_id = '0' OR store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY store_id ASC");

        foreach ($query->rows as $result) {
            if (!$result['serialized']) {
                $this->config->set($result['key'], $result['value']);
            } else {
                $this->config->set($result['key'], json_decode($result['value'], true));
            }
        }

        // Theme
        $this->config->set('template_cache', $this->config->get('developer_theme'));

        // Url
        $this->_registry->set('url', new Url($this->config->get('config_url'), $this->config->get('config_ssl')));

        // Language
        $code = '';

        $this->load->model('localisation/language');

        $languages = $this->model_localisation_language->getLanguages();

        if (isset($this->session->data['language'])) {
            $code = $this->session->data['language'];
        }

        if (isset($this->request->cookie['language']) && !array_key_exists($code, $languages)) {
            $code = $this->request->cookie['language'];
        }

        // Language Detection
        if (!empty($this->request->server['HTTP_ACCEPT_LANGUAGE']) && !array_key_exists($code, $languages)) {
            $detect = '';

            $browser_languages = explode(',', $this->request->server['HTTP_ACCEPT_LANGUAGE']);

            // Try using local to detect the language
            foreach ($browser_languages as $browser_language) {
                foreach ($languages as $key => $value) {
                    if ($value['status']) {
                        $locale = explode(',', $value['locale']);

                        if (in_array($browser_language, $locale)) {
                            $detect = $key;
                            break 2;
                        }
                    }
                }
            }

            if (!$detect) {
                // Try using language folder to detect the language
                foreach ($browser_languages as $browser_language) {
                    if (array_key_exists(strtolower($browser_language), $languages)) {
                        $detect = strtolower($browser_language);

                        break;
                    }
                }
            }

            $code = $detect ? $detect : '';
        }

        if (!array_key_exists($code, $languages)) {
            $code = $this->config->get('config_language');
        }

        if (!isset($this->session->data['language']) || $this->session->data['language'] != $code) {
            $this->session->data['language'] = $code;
        }

        if (!isset($this->request->cookie['language']) || $this->request->cookie['language'] != $code) {
            setcookie('language', $code, time() + 60 * 60 * 24 * 30, '/', $this->request->server['HTTP_HOST']);
        }

        // Overwrite the default language object
        $language = new Language($code);
        $language->load($code);

        $this->_registry->set('language', $language);

        // Set the config language_id
        $this->config->set('config_language_id', $languages[$code]['language_id']);

        // Customer
        $customer = new Cart\Customer($this->_registry);
        $this->_registry->set('customer', $customer);

        // Customer Group
        if (isset($this->session->data['customer']) && isset($this->session->data['customer']['customer_group_id'])) {
            // For API calls
            $this->config->set('config_customer_group_id', $this->session->data['customer']['customer_group_id']);
        } elseif ($this->customer->isLogged()) {
            // Logged in customers
            $this->config->set('config_customer_group_id', $this->customer->getGroupId());
        } elseif (isset($this->session->data['guest']) && isset($this->session->data['guest']['customer_group_id'])) {
            $this->config->set('config_customer_group_id', $this->session->data['guest']['customer_group_id']);
        }

        // Tracking Code
        if (isset($this->request->get['tracking'])) {
            setcookie('tracking', $this->request->get['tracking'], time() + 3600 * 24 * 1000, '/');

            $this->db->query("UPDATE `" . DB_PREFIX . "marketing` SET clicks = (clicks + 1) WHERE code = '" . $this->db->escape($this->request->get['tracking']) . "'");
        }

        // Currency
        $code = '';

        $this->load->model('localisation/currency');

        $currencies = $this->model_localisation_currency->getCurrencies();

        if (isset($this->session->data['currency'])) {
            $code = $this->session->data['currency'];
        }

        if (isset($this->request->cookie['currency']) && !array_key_exists($code, $currencies)) {
            $code = $this->request->cookie['currency'];
        }

        if (!array_key_exists($code, $currencies)) {
            $code = $this->config->get('config_currency');
        }

        if (!isset($this->session->data['currency']) || $this->session->data['currency'] != $code) {
            $this->session->data['currency'] = $code;
        }

        if (!isset($this->request->cookie['currency']) || $this->request->cookie['currency'] != $code) {
            setcookie('currency', $code, time() + 60 * 60 * 24 * 30, '/', $this->request->server['HTTP_HOST']);
        }

        $this->_registry->set('currency', new Cart\Currency($this->_registry));

        // Tax
        $this->_registry->set('tax', new Cart\Tax($this->_registry));

        if (isset($this->session->data['shipping_address'])) {
            $this->tax->setShippingAddress($this->session->data['shipping_address']['country_id'], $this->session->data['shipping_address']['zone_id']);
        } elseif ($this->config->get('config_tax_default') == 'shipping') {
            $this->tax->setShippingAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));
        }

        if (isset($this->session->data['payment_address'])) {
            $this->tax->setPaymentAddress($this->session->data['payment_address']['country_id'], $this->session->data['payment_address']['zone_id']);
        } elseif ($this->config->get('config_tax_default') == 'payment') {
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