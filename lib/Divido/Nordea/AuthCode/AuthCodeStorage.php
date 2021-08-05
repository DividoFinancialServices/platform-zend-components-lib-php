<?php

declare(strict_types=1);

namespace Divido\Nordea\AuthCode;

use Application;

interface AuthCodeStorage
{
    public function get(Application $application): AuthCode;
    public function persist(Application $application, AuthCode $authCode): void;
    public function revoke(Application $application): void;
}
