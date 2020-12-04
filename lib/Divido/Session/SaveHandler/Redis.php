<?php

class Divido_Session_SaveHandler_Redis implements Zend_Session_SaveHandler_Interface
{
    /**
     * @var Redis
     */
    protected $redis;

    /**
     * @var string
     */
    protected $prefix = 'session';

    /**
     * Divido_Session_SaveHandler_Redis constructor.
     *
     * @param Redis $redis
     * @param string $prefix
     */
    public function __construct(Redis $redis, string $prefix)
    {
        $this->redis = $redis;
        $this->prefix = $prefix;
    }

    public function __destruct()
    {
        Zend_Session::writeClose();
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * @param string $sid
     * @return bool
     */
    public function destroy($sid): bool
    {
        $key = $this->getKey($sid);
        $this->redis->del($key);

        return true;
    }

    /**
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime): bool
    {
        return true;
    }

    /**
     * @param string $save_path
     * @param string $name
     * @return bool
     */
    public function open($save_path, $name): bool
    {
        return true;
    }

    /**
     * @param string $id
     * @return string
     */
    public function read($id): string
    {
        $key = $this->getKey($id);
        if (!$this->redis->exists($key)) {
            return '';
        }

        return base64_decode($this->redis->get($key));
    }

    public function write($id, $data): bool
    {
        $lifetime = ini_get('session.gc_maxlifetime');
        $key = $this->getKey($id);
        $this->redis->setex($key, $lifetime, base64_encode($data));

        return true;
    }

    private function getKey($id): string
    {
        return "{$this->prefix}_{$id}";
    }
}
