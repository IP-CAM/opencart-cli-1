<?php

/**
 * Class AdminUserCli
 *
 * @author Istvan Dobrentei
 * @link https://www.dobrenteiistvan.hu
 */
class AdminUserCli extends BaseCommandCli
{
    private $_email;
    private $_password;
    private $_username;
    private $_firstname;
    private $_lastname;
    private $_user_group_id;
    protected $_init;

    /**
     * AdminUser constructor.
     *
     * @param $initClass Base
     * @param $action String
     * @param $params array
     */
    public function __construct($initClass, $action, $params)
    {
        $this->_username = isset($params['username']) ? $params['username'] : '';
        $this->_email = isset($params['email']) ? $params['email'] : '';
        $this->_password = isset($params['password']) ? $params['password'] : '';
        $this->_firstname = isset($params['firstname']) ? $params['firstname'] : '';
        $this->_lastname = isset($params['lastname']) ? $params['lastname'] : '';
        $this->_user_group_id = isset($params['user_group_id']) ? (int)$params['user_group_id'] : '';

        $this->_init = $initClass;
        $this->_init->load->model('user/user');
        $this->$action();
    }

    /**
     * Check user
     */
    protected function exist()
    {
        $result = empty($this->getUser()) ? 0 : 1;

        $this->result(self::SUCCESS_RESULT, $result);
    }

    /**
     * Create an admin user
     */
    protected function create()
    {
        $data['username'] = $this->_username;
        $data['password'] = $this->_password;
        $data['firstname'] = $this->_firstname;
        $data['lastname'] = $this->_lastname;
        $data['status'] = 0;
        $data['email'] = $this->_email;
        $data['user_group_id'] = $this->_user_group_id;
        $data['image'] = '';

        if($this->getUser())
        {
            $this->result(self::FAILED_RESULT, 'Existing user!');
            die;
        }

        $this->_init->model_user_user->addUser($data);

        $this->result(self::SUCCESS_RESULT, $this->_init->db->getLastId());
    }

    /**
     * Disable an admin user
     */
    protected function disable()
    {
        $user = $this->getUser();
        $user['status'] = 0;
        $this->_init->model_user_user->editUser($user['user_id'], $user);

        $this->result(self::SUCCESS_RESULT);
    }

    /**
     * Enable an admin user
     */
    protected function enable()
    {
        $user = $this->getUser();
        $user['status'] = 1;
        $this->_init->model_user_user->editUser($user['user_id'], $user);

        $this->result(self::SUCCESS_RESULT);
    }

    /**
     * Get user by username
     *
     * @return mixed
     */
    private function getUser()
    {
        return $this->_init->model_user_user->getUserByUsername($this->_username);
    }
}