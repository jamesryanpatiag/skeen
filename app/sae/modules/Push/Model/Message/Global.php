<?php

class Push_Model_Message_Global extends Core_Model_Default {

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Push_Model_Db_Table_Message_Global';
    }

    /**
     * Create global push.
     *
     * @param $params
     */
    public function createInstance($params) {

        $this->setTitle($params["title"]);
        $this->setMessage($params["message"]);
        $this->setSendToAll(!!$params["send_to_all"]);
        $this->setTargetApps(Siberian_Json::encode($params["checked"]));
        $this->setTargetDevices($params["devices"]);
        if(!!$params["open_url"]) {
            $this->setUrl($params["url"]);
        }

        $applications = Siberian_Json::decode($this->getTargetApps());
        if(!!$this->getSendToAll()) {
            $application_table = new Application_Model_Db_Table_Application();
            $all_applications = $application_table->findAllForGlobalPush();

            # Get apps that belong to the current admin
            $all_for_admin = $application_table->findAllByAdmin(
                $this->getSession()->getAdminId()
            )->toArray();

            $filtered = array_map(function($app) {
                return $app["app_id"];
            }, $all_for_admin);

            # We keep only apps that belongs to the admin
            $applications = array_intersect($all_applications, $filtered);
        }

        try {
            if(!empty($applications)) {
                $this->save();

                foreach($applications as $application_id) {
                    $application_id = intval($application_id);

                    $push_message = new Push_Model_Message();

                    $push_message->setMessageGlobalId($this->getId());
                    $push_message->setTargetDevices($this->getTargetDevices());
                    $push_message->setAppId($application_id);
                    $push_message->setSendToAll(true);
                    $push_message->setTitle($this->getTitle());
                    $push_message->setText($this->getMessage());
                    $push_message->setSendUntil(null);
                    $push_message->setBaseUrl($params["base_url"]);

                    if(!empty($this->getUrl())) {
                        $url = file_get_contents("https://tinyurl.com/api-create.php?url=".urlencode($this->getData("url")));
                        $push_message->setActionValue($url);
                    }

                    $push_message->save();
                }

                return true;

            } else {

                return false;
            }

        } catch(Exception $e) {

            # Add a log.

            return false;
        }

    }
}
