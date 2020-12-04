<?php
class Divido_Auth extends Zend_Auth
{
    public function __construct($namespace) {
        $this->setStorage(new Zend_Auth_Storage_Session($namespace));
        // do other stuff
    }
    static function getInstance() {
        throw new Zend_Auth_Exception('I do not support getInstance');
    }  

    public function authenticate(Zend_Auth_Adapter_Interface $adapter)
    {
        $result = $adapter->authenticate();

        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }

        return $result;
    }
}