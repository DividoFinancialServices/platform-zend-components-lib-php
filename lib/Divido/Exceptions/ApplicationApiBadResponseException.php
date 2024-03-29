<?php
class Divido_Exceptions_ApplicationApiBadResponseException extends \Exception
{
    /**
     * @var mixed
     */
    private $context;

    /**
     * ApplicationApiBadResponseException constructor.
     *
     * @param string $message
     * @param int $code
     * @param null $context
     * @param null $previous
     */
    public function __construct($message, $code, $context, $previous = null)
    {
        $code = $this->validateCode($code);

        if (!$code) {
            throw new \Exception("Divido Error codes must be 6 digits long.");
        }

        $this->context = $context;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Validates error code to ensure its a 6 digit code.
     *
     * @param $code
     * @return bool|int
     */
    private function validateCode($code)
    {
        $code = (int)$code;
        if (strlen(trim($code)) !== 6) {
            return false;
        }
        return $code;
    }
}