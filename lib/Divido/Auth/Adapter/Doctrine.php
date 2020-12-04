<?php
class Divido_Auth_Adapter_Doctrine implements Zend_Auth_Adapter_Interface
{

    /**
     *
     * @var array
     */
    protected $_criteria;

    /**
     *
     * @var string
     */
    protected $_account;

    /**
     *
     * @var \Doctrine\ORM\EntityRepository;
     */
    protected $_repository;

    /**
     *
     * @param \Doctrine\ORM\EntityRepository $repository
     * @param array $criteria
     * @return void
     */
    public function __construct(
        \Doctrine\ORM\EntityRepository $repository = null,
        array $criteria = array(), $account = false## disabla account
    ) {

        if (!is_null($repository)) {
            $this->setRepository($repository);
        }

        if (!empty($criteria)) {

            if (isset($criteria['merchant']) && $criteria['merchant']) {
                $table_merchant = Zend_Registry::get('em')->getRepository('Merchant')->findOneBy(['environment' => PLATFORM_ENVIRONMENT, 'id' => $criteria['merchant'], 'active' => true]);

                if (is_object($table_merchant)) {
                    $criteria['merchant'] = $table_merchant->getId();
                }
            }
            $this->setCriteria($criteria);
        }

        if (!is_object($account) && $account = 'admin') {
            $this->setAccount('admin');
        } else if (is_object($account)) {
            $this->setAccount($account);
        }
    }

    /**
     *
     * @param array $criteria
     * @return MyLib_Auth_Adapter_Doctrine
     */
    public function setCriteria(array $criteria)
    {
        $this->_criteria = $criteria;
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getCriteria()
    {
        return $this->_criteria;
    }

    /**
     *
     * @param string $account
     * @return string
     */
    public function setAccount($account)
    {
        $this->_account = $account;
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getAccount()
    {
        return $this->_account;
    }

    /**
     *
     * @param \Doctrine\ORM\EntityRepository $repository
     * @return MyLib_Auth_Adapter_Doctrine
     */
    public function setRepository(\Doctrine\ORM\EntityRepository $repository)
    {
        $this->_repository = $repository;
        return $this;
    }

    /**
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->_repository;
    }

    /**
     *
     * @throws Zend_Auth_Adapter_Exception
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        if (!is_array($this->_criteria)) {
            throw new Exception('Missing criteria!');
        }

        if (!($this->_repository instanceof \Doctrine\ORM\EntityRepository)) {
            throw new Exception('No Doctrine Repository set!');
        }

        $password = false;
        $checkPassword = false;

        if (isset($this->_criteria['password'])) {
            $password = $this->_criteria['password'];
            unset($this->_criteria['password']);
            $checkPassword = true;
        }

        $result = $this->getRepository()->findOneBy($this->_criteria);

        $identity = false;

        if (is_object($result) && $checkPassword) {

            if (substr($result->getPassword(), 0, 1) == '$' && password_verify($password, $result->getPassword())) {
                $identity = $result;
            } else if ($result->getPassword() == sha1($password . Zend_Registry::get('config')->app->password->salt)) {

                $result->setPassword(password_hash($password, PASSWORD_BCRYPT));
                Zend_Registry::get('em')->persist($result);
                Zend_Registry::get('em')->flush();

                $identity = $result;
            }
        } else if (is_object($result)) {
            $identity = $result;
        }

        if ($identity) {

            return new Zend_Auth_Result(
                Zend_Auth_Result::SUCCESS,
                $identity,
                ['Login successfull. (Login correct)']
            );
        } else {
            return new Zend_Auth_Result(
                Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID,
                [],
                ['Login failed. (Login incorrect)']
            );
        }

    }
}
