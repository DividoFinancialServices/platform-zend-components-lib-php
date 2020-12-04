<?php
class Divido_Plugin_Registry extends Zend_Controller_Plugin_Abstract
{
    public function __construct()
    {
    
    }
    
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {    	
		$moduleName = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
		$controllerName = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
		$actionName = Zend_Controller_Front::getInstance()->getRequest()->getActionName();

    	Zend_Registry::set('moduleName',$moduleName);
    	Zend_Registry::set('controllerName',$controllerName);
    	Zend_Registry::set('actionName',$actionName);
    }
    
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {      
        
    }

}