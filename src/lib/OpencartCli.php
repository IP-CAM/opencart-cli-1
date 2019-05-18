<?php

/**
 * Class OpencartCli
 *
 * @author Istvan Dobrentei
 * @link https://www.dobrenteiistvan.hu
 */
class OpencartCli
{
    const VERSION = '0.1';
    const CONTEXT_ADMIN = 'admin';
    const CONTEXT_CATALOG = 'catalog';

    const COMMAND_ADMIN_USER_CREATE     = 'admin:user:create';
    const COMMAND_ADMIN_USER_DISABLE    = 'admin:user:disable';
    const COMMAND_ADMIN_USER_ENABLE     = 'admin:user:enable';
    const COMMAND_ADMIN_USER_EXIST      = 'admin:user:exist';
    const COMMAND_PRODUCT_SET_PRICE     = 'product:set:price';
    const COMMAND_PRODUCT_SET_QUANTITY  = 'product:set:quantity';
    const COMMAND_ORDER_SET_STATUS      = 'order:set:status';
    const COMMAND_CUSTOMER_DISABLE      = 'customer:set:disable';
    const COMMAND_CONFIG_CACHE_CLEAN    = 'config:cache:clean';
    const COMMAND_RUN_CLASS             = 'run:class';

    const ALL_PARAMS = array(
        self::COMMAND_ADMIN_USER_CREATE => array('r' => array('username', 'password', 'user_group_id'), 'o' => array('email', 'lastname', 'firstname')),
        self::COMMAND_ADMIN_USER_DISABLE => array('r' => array('username')),
        self::COMMAND_ADMIN_USER_ENABLE => array('r' => array('username')),
        self::COMMAND_ADMIN_USER_EXIST => array('r' => array('username')),
        self::COMMAND_PRODUCT_SET_PRICE => array('r' => array('id:sku', 'value')),
        self::COMMAND_PRODUCT_SET_QUANTITY => array('r' => array('id:sku', 'value')),
        self::COMMAND_ORDER_SET_STATUS => array('r' => array('id', 'statusid')),
        self::COMMAND_CUSTOMER_DISABLE => array('r' => array('email')),
        self::COMMAND_CONFIG_CACHE_CLEAN => array('r' => array()),
        self::COMMAND_RUN_CLASS => array('r' => array('name'), 'o' => array('cpath')),
        'basic' => array('r' => array('path'),'o' =>array('context', 'version', 'cmd'))
    );

    protected $_path;
    protected $_version;
    protected $_context;

    public function __construct($params)
    {
        ############################################################ BASIC -->
        if(isset($params['help']) && !isset($params['cmd']))
        {
            $this->showUsage();
            die;
        }
        elseif(isset($params['cmd']) && $params['cmd'] == '--help')
        {
            $this->showCmdHelp();
            die;
        }

        if(isset($params['version']))
        {
            echo 'v' . self::VERSION;
            die;
        }

        $this->_path = isset($params['path']) ? $params['path'] : null;

        if(empty($this->_path))
        {
            $this->showUsage('You should set the path parameter!');
            die;
        }

        $this->_version = $this->getVersionNumber();

        if(empty($this->_version))
        {
            echo 'Cannot detect version number!';
            die;
        }

        $this->_context = isset($params['context']) && in_array($params['context'],
            array(self::CONTEXT_ADMIN, self::CONTEXT_CATALOG)) ? $params['context'] : self::CONTEXT_ADMIN;

        ########################################################### COMMANDS -->

        if(isset($params['cmd']))
        {
            $command = explode(':', $params['cmd']);

            if(isset($command[0]) && $command[0] == 'admin')
            {
                $this->_context = self::CONTEXT_ADMIN;
            }

            //init environment
            $initClass = $this->init();

            if($this->isCmd($params, self::COMMAND_ADMIN_USER_CREATE) ||
                $this->isCmd($params, self::COMMAND_ADMIN_USER_ENABLE) ||
                $this->isCmd($params, self::COMMAND_ADMIN_USER_DISABLE) ||
                $this->isCmd($params, self::COMMAND_ADMIN_USER_EXIST)
            )
            {
                $param['username'] = isset($params['username']) ? $params['username'] : '';
                $param['password'] = isset($params['password']) ? $params['password'] : '';
                $param['email'] = isset($params['email']) ? $params['email'] : '';
                $param['lastname'] = isset($params['lastname']) ? $params['lastname'] : '';
                $param['firstname'] = isset($params['firstname']) ? $params['firstname'] : '';
                $param['user_group_id'] = isset($params['user_group_id']) ? $params['user_group_id'] : '';

                new AdminUserCli($initClass, $command[2], $param);
            }
            if($this->isCmd($params, self::COMMAND_PRODUCT_SET_PRICE) ||
                $this->isCmd($params, self::COMMAND_PRODUCT_SET_QUANTITY))
            {
                $param['id'] = isset($params['id']) ? $params['id'] : '';
                $param['model'] = isset($params['model']) ? $params['model'] : '';
                $param['sku'] = isset($params['sku']) ? $params['sku'] : '';
                $param['value'] = isset($params['value']) ? $params['value'] : '';

                new ProductCli($initClass, $command[2], $param);
            }
            if($this->isCmd($params, self::COMMAND_CONFIG_CACHE_CLEAN))
            {
                new CacheCli($initClass, $command[2], []);
            }
            if($this->isCmd($params, self::COMMAND_RUN_CLASS))
            {
                $classPath = !isset($params['cpath']) ? $this->_path . '/cli/' : $params['cpath'] . '/';

                require_once $classPath . $params['name'] . '.php';

                new $params['name']($initClass);
            }
        }
    }

    /**
     * Checking input containts all required params
     *
     * @param $params
     * @param $cmd
     * @return bool
     */
    private function isCmd($params, $cmd)
    {
        $valid = false;
        if(isset($params['cmd']) && $params['cmd'] == $cmd)
        {
            $requireds = self::ALL_PARAMS[$cmd]['r'];

            foreach($requireds as $required)
            {
                $orParams = explode(':', $required);
                foreach($orParams as $orParam)
                {
                    $partValid = isset($params[$orParam]);
                    if($partValid)
                    {
                        return true;
                    }
                }
                if(!$partValid)
                {
                    return false;
                }
            }

            $valid = 0 == count(array_diff($required, array_keys($params)));
        }

        return $valid;
    }

    /**
     * Init environment
     *
     * @return v15Cli|v20Cli|v30Cli
     */
    protected function init()
    {
        if($this->hasPrefix($this->_version, '15'))
        {
            $init = new v15Cli($this->_path, $this->_context);
        }
        if($this->hasPrefix($this->_version, '2'))
        {
            $init = new v20Cli($this->_path, $this->_context);
        }
        if($this->hasPrefix($this->_version, '3'))
        {
            $init = new v30Cli($this->_path, $this->_context);
        }

        return $init;
    }

    protected function hasPrefix($string, $prefix)
    {
        return substr($string, 0, strlen($prefix)) == $prefix;
    }

    protected function showUsage($message = '')
    {
        echo $message . PHP_EOL;
        echo 'Usage:' . PHP_EOL;
        echo '--version' . ' show version number' . PHP_EOL;
        echo '--help' . ' show this help' . PHP_EOL;
        echo '--path' . ' The absolute path to the opencart root directory without trailing slash.' . PHP_EOL;
        echo '--context' . ' admin or catalog (catalog is the default)' . PHP_EOL;
        echo '--cmd' . ' store related commands (more --help)' . PHP_EOL;
    }

    protected function showCmdHelp()
    {
        echo 'Available commands:' . PHP_EOL;
        echo '=> ' . self::COMMAND_ADMIN_USER_CREATE . ' params:' . PHP_EOL;
        echo '=> ' . self::COMMAND_ADMIN_USER_DISABLE . ' params:'.PHP_EOL;
        echo '=> ' . self::COMMAND_ADMIN_USER_ENABLE . ' params:'.PHP_EOL;
        echo '=> ' . self::COMMAND_CONFIG_CACHE_CLEAN . PHP_EOL;
    }


    /**
     * Detect version number and return
     *
     * @return string
     */
    protected function getVersionNumber()
    {
        $content = file_get_contents($this->_path . '/index.php');
        $lines = preg_split("/((\r?\n) | (\r\n))/", $content);

        foreach($lines as $line)
        {
            if(strpos($line, 'VERSION') !== false)
            {
                $version = $this->getText($line, 'define', ';');
                $item = explode(',', $version);
                $vString = isset($item[1]) ? rtrim(rtrim(ltrim($item[1], '\' \"'), ')'), '\'') : '';
                return str_replace('.', '', $vString);
                break;
            }
        }
    }

    protected function getText($string, $start, $end)
    {
        $r = explode($start, $string);

        if(isset($r[1]))
        {
            $r = explode($end, $r[1]);
            return $r[0];
        }

        return '';
    }

    /**
     * Get all possible parameters
     *
     * @return array
     */
    public static function getParams()
    {
        $params = array();
        foreach(self::ALL_PARAMS as $commands => $param)
        {
            if(isset($param['r']))
            {
                foreach($param['r'] as $required)
                {
                    $items = explode(':', $required);
                    $params = array_merge($params, array_values($items));
                }
            }
            if(isset($param['o']))
            {
                $params = array_merge($params, $param['o']);
            }
        }
        $params = array_unique($params);
        $result = array();
        foreach($params as $item)
        {
            array_push($result, $item.":");
        }

        return $result;
    }
}