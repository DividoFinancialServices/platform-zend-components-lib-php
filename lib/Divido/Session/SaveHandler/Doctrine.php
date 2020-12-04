<?php
/**
 * Manages sessions storage through \Doctrine\ORM\EntityManager
 * @author Ocramius
 */

class Divido_Session_SaveHandler_Doctrine implements Zend_Session_SaveHandler_Interface {

    const SESSION_ENTITY_NAME = 'Session';

    protected $_logger;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $_em;

    /**
     * @var \Deneb\Entity\Session
     */
    protected $_session;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $_sessionRepository;

    /**
     * @var int
     */
    protected $_sessionLifetime;

    /**
     * @var string
     */
    protected $_sessionSavePath;

    /**
     * @var string
     */
    protected $_sessionName;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(\Doctrine\ORM\EntityManager $em) {
        $this->_em                  = $em;
        $this->_sessionRepository   = $this->_em->getRepository(self::SESSION_ENTITY_NAME);
    }

    public function __destruct() {
        \Zend_Session::writeClose();
    }

    /**
     * @return bool
     */
    public function close() {
        return true;
    }

    /**
     * @param string $sid
     * @return bool
     */
    public function destroy($sid) {
        //$this->_logEvent('trying to destroy $sid ' . ($sid ? $sid : 'NULL'));
        $session = $this->_getSessionEntity($sid);
        if($this->_em->getUnitOfWork()->getEntityState($session) === \Doctrine\ORM\UnitOfWork::STATE_MANAGED) {
            //$this->_logEvent('destroying $sid ' . ($sid ? $sid : 'NULL'));
            $this->_em->remove($session);
            $this->_em->flush();
            $session = $this->_getSessionEntity();
        }
        return true;
    }

    /**
     * @return bool
     */
    public function gc($maxlifetime) {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->delete(self::SESSION_ENTITY_NAME, 's')
            ->where($qb->expr()->lt('s.expires', ':expires'))
            ->setParameter('expires', new \DateTime(), \Doctrine\DBAL\Types\Type::DATETIME)
            ->getQuery()
            ->execute();
        return true;
    }

    /**
     * @return bool
     */
    public function open($save_path, $name) {
        $this->_sessionSavePath = $save_path;
        $this->_sessionName     = $name;
        return true;
    }

    /**
     * @param string $id
     * @return string
     */
    public function read($id) {
        $session = $this->_getSessionEntity($id);
        //$this->_logEvent('reading sid ' . ($id ? $id : 'NULL') . ' - ' . $session->toJSON());
        if(($session->getLastModified()->getTimestamp() + $session->getLifetime()) > \time()) {
            return $session->getData();
        } else if($session->getSid()) {
            $this->destroy($id);
        }
        return '';
    }

    public function write($id, $data) {
        //$this->_logEvent('writing sid ' . ($id ? $id : 'NULL'));
        $session = $this->_getSessionEntity($id);
        $session->setLifetime($this->_sessionLifetime ? $this->_sessionLifetime : ini_get('session.gc_maxlifetime'));
        $session->setSavepath($this->_sessionSavePath);
        $session->setName($this->_sessionName);
        $session->setLastModified(new \DateTime());
        $session->setData($data);
        $session->setSid($id);
        if($this->_em->getUnitOfWork()->getEntityState($session, \Doctrine\ORM\UnitOfWork::STATE_NEW) !== \Doctrine\ORM\UnitOfWork::STATE_MANAGED) {
            //$this->_logEvent('persisting sid ' . ($id ? $id : 'NULL') . ' - ' . $session->toJSON());
            $this->_em->persist($session);
        }
        try {
            $this->_em->flush();
        }catch(\Exception $e){
            \ob_start();
            echo "\n\n\n===============\n\n";
            \Doctrine\Common\Util\Debug::dump($e);
            \Doctrine\Common\Util\Debug::dump($session);
            \Doctrine\Common\Util\Debug::dump($this->_em->getUnitOfWork()->getEntityState($session));
            \Zend_Log::factory(array(
                array(
                    'writerName'   => 'Stream',
                    'writerParams' => array(
                        'stream'   =>
                            \APPLICATION_ROOT
                            . \DIRECTORY_SEPARATOR
                            . 'log'
                            . \DIRECTORY_SEPARATOR
                            . 'session.error.log',
                    )
                )
            ))->crit(\ob_get_clean());
            \ob_end_clean();
            throw new \Deneb\Exception('Session flush operation failed!', 500, $e);
        }
    }

    /**
     * @return \Deneb\Entity\Session
     */
    public function getSession(){
        return $this->_getSessionEntity();
    }
    
    /**
     * @param string $sid
     * @return \Deneb\Entity\Session
     */
    protected function _getSessionEntity($sid = null) {
        if($sid !== null) {
            if(
                $this->_session instanceof \Deneb\Entity\Session
                && $this->_session->getSid() !== $sid
            ) {
                $this->_em->remove($this->_session);
                $this->_session = null;
            }
            if(!($this->_session instanceof \Deneb\Entity\Session)) {
                $this->_session = $this->_sessionRepository->findOneBy(array('sid' => $sid));
            }
        }
        if(!($this->_session instanceof \Deneb\Entity\Session)) {
            //$this->_logEvent('creating session for sid ' . ($sid ? $sid : 'NULL'));
            $this->_session = new \Deneb\Entity\Session();
            $this->_session->setSid($sid);
        }
        return $this->_session;
    }

    protected function _logEvent($eventString) {
        if($this->_logger === null) {
            $this->_logger = \Zend_Log::factory(array(
                array(
                    'writerName'   => 'Stream',
                    'writerParams' => array(
                        'stream'   =>
                            \APPLICATION_ROOT
                            . \DIRECTORY_SEPARATOR
                            . 'log'
                            . \DIRECTORY_SEPARATOR
                            . 'session.debug.log',
                    )
                )
            ));
        }
        try {
            throw new \Deneb\Exception('nothing', 200);
        }catch(\Deneb\Exception $e) {
            $this->_logger->crit("\n\n=====\n" . $eventString . "\n=====\n" . $e->getTraceAsString());
        }
    }
    
}