<?php

class ScreenshotsGenerator_Mobile_ViewController extends Application_Controller_Mobile_Default {

	public function checktokenAction() {
    	$html = array(
            "success" => 0,
        );
    	$appId = $this->getRequest()->getParam("app_id", null);
		$token = $this->getRequest()->getParam("token", null);
		if($this->gentoken($appId) == $token)
			$html['success'] = 1;
    
    	$this->_sendHtml($html);
    }
    
    public function gentoken($appId){
    	$application = new Application_Model_Application();

    	$application = $application->find($appId);
    	return $token = md5(date("h").$application->getKey().$application->getCreatedAt().$application->getUpdatedAt());
    }
}
