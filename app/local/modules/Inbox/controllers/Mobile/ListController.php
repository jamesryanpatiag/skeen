<?php

class Inbox_Mobile_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            try {

                $data = array(
                    "collection" => array(),
                    "page_title" => __("Inbox"),
                    "displayed_per_page" => Inbox_Model_Application_Message::DISPLAYED_PER_PAGE
                );

                if($customer_id = $this->getRequest()->getParam("customer_id")) {
                    $offset = $this->getRequest()->getParam("offset", 0);

                    $messages = new Inbox_Model_CustomerMessage();
                    $messages = $messages->findByCustomerId($value_id, $customer_id, $offset, "mobile");

                    $json = array();
                    foreach($messages as $message) {
                        $message_data = $message->getdata();

                        $message_data["send_at"] = datetime_to_format($message_data["latest_update"], Zend_Date::DATETIME_SHORT);
                        if(!empty($message_data["thumbnail"]) && is_readable(Core_Model_Directory::getBasePathTo("/images/application".$message_data["thumbnail"]))) {
                            $message_data["picture"] = $this->getRequest()->getBasePath()."/images/application".$message_data["thumbnail"];
                        } else {
                            $message_data["picture"] = $this->getApplication()->getIcon(100) ? $this->getRequest()->getBasePath().$this->getApplication()->getIcon(100) : null;
                        }

                        $message_data["is_new"] = !!$message_data["is_new"];
                        $message_data["has_new_reply"] = !!$message_data["has_new_reply"];

                        $json[] = $message_data;
                    }

                    $data["collection"] = $json;
                }

                $option_model = new Inbox_Model_Option();
                $options = $option_model->find($value_id, "value_id");
                $data["settings"] = array(
                    "display_card"          => (($options->getDisplayType() == "card") || ($options->getDisplayType() == "compact-card")),
                    "display_compact"       => ($options->getDisplayType() == "compact"),
                    "display_compact_card"  => ($options->getDisplayType() == "compact-card"),
                    "answer_from"           => $options->getAnswerFrom(),
                );

            }
            catch(Exception $e) {
                $data = array(
                    "error" => true,
                    "message" => $e->getMessage()
                );
            }

        } else {
            $data = array(
                "error" => true,
                "message" => __("An error occurred during process. Please try again later.")
            );
        }

        $this->_sendJson($data);

    }


}