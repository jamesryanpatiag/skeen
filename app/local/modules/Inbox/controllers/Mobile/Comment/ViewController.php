<?php

class Inbox_Mobile_Comment_ViewController extends Application_Controller_Mobile_Default {

    public function findAction() {
        $request = $this->getRequest();

        if(($message_id = $request->getParam("message_id")) &&
            ($customer_id = $request->getParam("customer_id"))) {

            try {

                $offset = $request->getParam("offset", 0);

                $message = new Inbox_Model_Application_Message();
                $message->find($message_id);

                $replies = new Inbox_Model_Reply();
                $replies = $replies->findByMessageId($message_id, $customer_id, true, $offset, true);

                $data = array(
                    "collection"            => array(),
                    "page_title"            => __("Inbox - Replies"),
                    "parent_is_deleted"     => !$message->getIsVisibleInEditor(),
                    "icon_url"              => $this->_getImage("inbox/"),
                    "displayed_per_page"    => Inbox_Model_Application_Reply::DISPLAYED_PER_PAGE
                );

                $json = array();
                foreach($replies as $reply) {
                    $message_data               = $reply->getData();
                    $message_data["send_at"]    = datetime_to_format($message_data["created_at"]);
                    $json[]                     = $message_data;
                }

                $data["collection"] = $json;

            }
            catch(Exception $e) {
                $data = array(
                    "error" => true,
                    "message" => $e->getMessage(),
                );
            }

        } else {
            $data = array(
                "error" => true,
                "message" => __("An error occurred during process. Please try again later."),
            );
        }

        $this->_sendJson($data);

    }

    public function postcommentAction() {

        if($message = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                $reply = new Inbox_Model_Application_Reply();
                $parent = new Inbox_Model_Application_Message();
                $parent->find($message["message_id"]);

                $data = array(
                    "parent_id" => $message["message_id"],
                    "message" => $message["message"],
                    "customer_id" => $message["customer_id"]
                );

                $reply->setData($data)->save();

                // Sending mail to admin
                $admin = new Admin_Model_Admin();
                $admin->find($parent->getAdminId());

                $inbox_option_model = new Inbox_Model_Option();
                $inbox_option = $inbox_option_model->find($parent->getValueId(), "value_id");

                $global_emails = explode(";", str_replace(",", ";", $inbox_option->getRecipientEmail()));
                $send_email_to = $inbox_option->getSendEmailTo();

                if($send_email_to != "disabled") {
                    $customer = new Customer_Model_Customer();
                    $customer->find($message["customer_id"]);

                    # @version 4.8.7 - SMTP
                    $mail = new Siberian_Mail();
                    $mail->setBodyHtml($message["message"]);
                    $mail->setFrom($customer->getEmail(), $customer->getFirstname()." ".$customer->getLastname());

                    switch($send_email_to) {
                        case "admin":
                                if($admin->getId()) {
                                    $mail->addTo($admin->getEmail(), $admin->getFirstname()." ".$admin->getLastname());
                                }
                            break;
                        case "recipient":
                                foreach($global_emails as $global_email) {
                                    $mail->addTo($global_email);
                                }
                            break;
                        case "all":
                                foreach($global_emails as $global_email) {
                                    $mail->addTo($global_email);
                                }
                                if($admin->getId()) {
                                    $mail->addTo($admin->getEmail(), $admin->getFirstname()." ".$admin->getLastname());
                                }
                            break;
                    }

                    $mail->setSubject(__("A new comment to your message %s", $parent->getTitle()));
                    $mail->send();
                }

                $data = array(
                    "success" => true
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