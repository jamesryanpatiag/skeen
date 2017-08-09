<?php

class ScreenshotsGenerator_Backoffice_ListController extends Backoffice_Controller_Default
{

    public function loadAction() {

        $html = array(
            "title" => __("Screenshots Generator"),
            "icon" => "fa-image",
        );

        $this->_sendHtml($html);

    }
    
    public function checkcreditsAction() {
    	$appkey = $this->getRequest()->getParam("appkey", null);
    	$app_id = $this->getRequest()->getParam("app_id", null);
		$token = $this->gentoken($app_id);

    	$html = array(
            "success" => 0, //0 - 1 credit required, 1: no credit required , -1 : no credits available
            "message" => "1 Credits will be consumed for each app screenshots download",
            "token" => $token
        );
        $creditCheckUrl = "http://store.pepoapp.com/screenshots/credits.php?domain=".$_SERVER['HTTP_HOST']."&appkey=$appkey&app_id=$app_id&token=$token";
        $creditsData = @file_get_contents($creditCheckUrl);
        $creditsData = json_decode($creditsData,true);

        if(isset($creditsData['creditsRequired'])){
        
        if($creditsData['creditsRequired']>0 && $creditsData['creditsAvailable']>0)
        	$html['success'] = 0;
        	
         if($creditsData['creditsRequired']>0 && $creditsData['creditsAvailable']==0)
        	$html['success'] = -1; //need to buy credits
        	
         if($creditsData['creditsRequired']==0)
        	$html['success'] = 1; //no credits required
        	

				
		 $html['message'] =$creditsData['message'];
				
			
        }
        

        $this->_sendHtml($html);

    }

    public function findallAction() {
        $application = new Application_Model_Application();

        $offset = $this->getRequest()->getParam("offset", null);
        $limit = Application_Model_Application::BO_DISPLAYED_PER_PAGE;

        $request = $this->getRequest();
        if($range = $request->getHeader("Range")) {
            $parts = explode("-", $range);
            $offset = $parts[0];
            $limit = ($parts[1] - $parts[0]) + 1;
        }


        $params = array(
            "offset" => $offset,
            "limit" => $limit
        );

        $filters = array();
        if($_filter = $this->getRequest()->getParam("filter", false)) {
            $filters["(name LIKE ? OR app_id LIKE ? OR bundle_id LIKE ? OR package_name LIKE ?)"] = "%{$_filter}%";
        }

        $order = $this->getRequest()->getParam("order", false);
        $by = filter_var($this->getRequest()->getParam("by", false), FILTER_VALIDATE_BOOLEAN);
        if($order) {
            $order_by = ($by) ? "ASC" : "DESC";
            $order = sprintf("%s %s", $order, $order_by);
        }

        $to_publish = filter_var($this->getRequest()->getParam("toPublish", false), FILTER_VALIDATE_BOOLEAN);
        if($to_publish) {
            $app_ids = $application->findAllToPublish();

            if(empty($app_ids)) {
                $filters["app_id = ?"] = -1;
            } else {
                $filters["app_id IN (?)"] = $app_ids;
            }
        }

        $total = $application->countAll($filters);

        if($range = $request->getHeader("Range")) {
            $start = $parts[0];
            $end = ($total <= $parts[1]) ? $total : $parts[1];

            $this->getResponse()->setHeader("Content-Range", sprintf("%s-%s/%s", $start, $end, $total));
            $this->getResponse()->setHeader("Range-Unit", "items");
        }

        $applications = $application->findAll($filters, $order, $params);

        $data = array(
            "display_per_page"=> $limit,
            "collection" => array()
        );

        foreach($applications as $application) {
            $data["collection"][] = array(
                "id" => $application->getId(),
                "can_be_published" => in_array($application->getId(), $app_ids),
                "name" => mb_convert_encoding($application->getName(), 'UTF-8', 'UTF-8'),
                "bundle_id" => $application->getBundleId(),
                "package_name" => $application->getPackageName(),
                "icon" => $application->getIcon(128),
                "url_key" => $application->getKey(),
                'app_store_icon' => $application->getAppStoreIcon(),
             	'google_play_icon' => $application->getGooglePlayIcon(),
            );
        }

        $this->_sendHtml($data["collection"]);

    }
    
    public function gentoken($appId){
    	$application = new Application_Model_Application();

    	$application = $application->find($appId);
    	return $token = md5(date("h").$application->getKey().$application->getCreatedAt().$application->getUpdatedAt());
    }

    

}
