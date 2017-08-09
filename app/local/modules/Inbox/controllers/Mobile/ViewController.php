<?php

class Inbox_Mobile_ViewController extends Application_Controller_Mobile_Default {

    public function findAction() {

        if($message_id = $this->getRequest()->getParam("message_id")) {

            try {

                $message = new Inbox_Model_Application_Message();
                $message->find($message_id);

                $page_model = new Cms_Model_Application_Page();
                $page = $page_model->find($message->getPageId());

                $blocks = $page->getBlocks();

                $json = array();

                foreach($blocks as $block) {
                    $json[] = $block->_toJson($this->getRequest()->getBaseUrl());
                }

                $item = $message->getData();
                $item["created_at"] = datetime_to_format($item["created_at"], Zend_Date::DATETIME_SHORT);

                $message_customer_model = new Inbox_Model_CustomerMessage();
                $message_customer = $message_customer_model->find(array(
                    "message_id" => $message_id,
                    "customer_id" => $this->getSession()->getCustomerId(),
                ));

                # Save the message is read.
                $message_customer
                    ->setIsNew(false)
                    ->setHasNewReply(false)
                    ->save();

                $item["is_visible_in_editor"] = !!($message_customer->getIsVisibleInEditor());
                $item["is_visible_in_mobile"] = !!($message_customer->getIsVisibleInMobile());

                $data = array(
                    "item"                  => $item,
                    "page_title"            => __("Inbox"),
                    "delete_message"        => __("Are you sure to delete this message?"),
                    "title_delete_message"  => __("Delete message"),
                    "blocks"                => $json,
                    "icon_url"              => $this->_getImage("inbox/")
                );

            }
            catch(Exception $e) {
                $data = array(
                    "error" => true,
                    'message' => $e->getMessage()
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

    public function deleterootmessageAction() {

        if($message_id = $this->getRequest()->getParam("message_id")) {

            try {
                $message = new Inbox_Model_CustomerMessage();
                $message->find(array(
                    "message_id" => $message_id,
                    "customer_id" => $this->getSession()->getCustomerId()
                ));

                if(!$message->getId()) {
                    throw new Siberian_Exception(__('An error occurred during the process. Please try again later.'));
                }

                $message
                    ->setIsVisibleInMobile(false)
                    ->save();

                $data = array(
                    "success" => 1
                );

            } catch(Exception $e) {
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