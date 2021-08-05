<?php

declare(strict_types=1);

namespace Divido\Nordea\AuthCode;

use Application;

class SessionStorage implements AuthCodeStorage
{
    /**
     * @var \Zend_Session_Namespace
     */
    private $session;

    public function __construct(\Zend_Session_Namespace $session)
    {
        $this->session = $session;
    }

    public function get(Application $application): AuthCode
    {
        $key = $this->getKey($application);

        return new AuthCode($this->session->$key, time() + 60);
    }

    public function persist(Application $application, AuthCode $authCode): void
    {
        $key = $this->getKey($application);

        $this->session->$key = $authCode->getValue();
        $this->session->setExpirationSeconds($authCode->getExpiry() - time(), $key);
    }

    public function revoke(Application $application): void
    {
        $key = $this->getKey($application);

        unset($this->session->$key);
    }

    private function getKey(Application $application): string
    {
        return "auth_code::{$application->getId()}";
    }
}
