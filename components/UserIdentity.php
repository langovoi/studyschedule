<?php

class UserIdentity extends CUserIdentity
{

    private $_id;
    public $name;

    /**
     * @return boolean
     */
    public function authenticate()
    {
        $user = new Users('update');
        $user = $user->findByAttributes([], 'username = :username OR email = :username', ['username' => strtolower($this->username)]);

        if (!$user)
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        elseif (strtolower($user->password) !== strtolower($this->password)) {
            $this->errorCode = self::ERROR_PASSWORD_INVALID;
        } else {
            $this->username = $user->username;
            $this->_id = $user->id;
            $this->errorCode = self::ERROR_NONE;
        }
        return !$this->errorCode;
    }

    public function getId()
    {
        return $this->_id;
    }
}