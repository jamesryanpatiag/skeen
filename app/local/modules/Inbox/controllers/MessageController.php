<?php

/**
 * Class Inbox_MessageController
 */
class Inbox_MessageController extends Application_Controller_Default {

    /**
     * Load form edit
     */
    public function loadformAction() {
        $message_id = $this->getRequest()->getParam("message_id");
        $customer_id = $this->getRequest()->getParam("customer_id");

        $inbox_message = new Inbox_Model_Message();
        $inbox_message->find($message_id);

        if($inbox_message->getId()) {
            $inbox_customer_message = new Inbox_Model_CustomerMessage();
            $inbox_customer_message->find($inbox_message->getId(), "message_id");

            $inbox_reply_model = new Inbox_Model_Reply();
            $inbox_replies = $inbox_reply_model->findByMessageId($inbox_message->getId(), $customer_id);

            $form = new Inbox_Form_Reply();

            $form->populate(array(
                "parent_id" => $inbox_message->getId(),
                "admin_id" => $this->getSession()->getAdminId(),
                "is_visible_in_editor" => true,
                "customer_id" => $customer_id,
            ));

            $form->setValueId($this->getCurrentOptionValue()->getId());

            # This form is special and needs more javascript, so we made a partial
            $html = $this->getLayout()
                ->addPartial("location_form", 'Core_View_Default', 'inbox/application/reply.phtml')
                ->setReplyForm($form)
                ->setMessage($inbox_message)
                ->setReplies($inbox_replies)
                ->toHtml();

            $data = array(
                "success"   => 1,
                "form"      => $html,
                "message"   => __("Success."),
            );
        } else {
            /** Do whatever you need when form is not valid */
            $data = array(
                "error"     => 1,
                "message"   => __("The Reply you are trying to edit doesn't exists."),
            );
        }

        $this->_sendJson($data);
    }

    /**
     * Create/Edit Message
     *
     * @throws exception
     */
    public function editpostAction() {
        $values = $this->getRequest()->getParams();
        $option_value = $this->getCurrentOptionValue();
        $value_id = $option_value->getId();

        $form = new Inbox_Form_Message();
        if($form->isValid($values)) {
            # Create the cms/page/blocks
            $page_model = new Cms_Model_Application_Page();
            $page = $page_model->edit_v2($option_value, $values);

            # Create the message
            if(empty($values["send_at"])) {
                $values["send_at"] = null;
            } else {
                $locale = $values["datepicker_format"];
                $date = new Zend_Date();
                $date->set($values["send_at"], null, new Zend_Locale($locale));
                $values["send_at"] = $date->toString("YYYY-MM-dd HH:mm:ss");
            }

            $values["thumbnail"] = Siberian_Feature::saveImageForOptionDelete($option_value, $values["thumbnail"]);

            $inbox_message = new Inbox_Model_Message();
            $inbox_message->addData($values);
            $inbox_message->setPageId($page->getId());
            $inbox_message->save();

            # Send the message to the concerned users
            $this->_sendToUsers($inbox_message, $values["customer_id"]);

            if($values["send_notif"]) {
                $this->_pushToUsers($value_id, $values["send_at"], $values["customer_id"], $inbox_message);
            }

            $data = array(
                "success" => 1,
                "message" => __("Success."),
            );
        } else {
            /** Do whatever you need when form is not valid */
            $data = array(
                "error"     => 1,
                "message"   => $form->getTextErrors(),
                "errors"    => $form->getTextErrors(true),
            );
        }

        $this->_sendJson($data);
    }

    /**
     * Delete Message
     */
    public function deletepostAction() {
        $values = $this->getRequest()->getPost();

        $form = new Inbox_Form_Message_Delete();
        if($form->isValid($values)) {
            $inbox_message = new Inbox_Model_CustomerMessage();
            $inbox_message->find(array(
                "message_id" => $values["message_id"],
                "customer_id" => $values["customer_id"]
            ));
            $inbox_message
                ->setIsVisibleInEditor(false)
                ->save();

            $data = array(
                "success"           => 1,
                "success_message"   => __("Message successfully deleted."),
                "message_loader"    => 0,
                "message_button"    => 0,
                "message_timeout"   => 2
            );
        } else {
            $data = array(
                "error"     => 1,
                "message"   => $form->getTextErrors(),
                "errors"    => $form->getTextErrors(true),
            );
        }

        $this->_sendJson($data);
    }

    /**
     * @param $inbox_message
     * @param $customers
     */
    protected function _sendToUsers($inbox_message, $customers = array()) {
        foreach ($customers as $customer) {
            $customer_message = new Inbox_Model_Application_Customer_Message();
            $customer_message_data = array(
                "customer_id" => $customer,
                "message_id" => $inbox_message->getId()
            );
            $customer_message->setData($customer_message_data);
            $customer_message->save();
        }
    }

    /**
     * @param $value_id
     * @param null $send_at
     * @param array $customers
     * @param null $inbox_message
     */
    protected function _pushToUsers($value_id, $send_at = null, $customers = array(), $inbox_message = null) {

        if (Push_Model_Message::hasIndividualPush()) {
            $message_push = new Push_Model_Message();
            $message_push->setMessageType(Push_Model_Message::TYPE_PUSH);

            $data_push = array(
                "title"             => __("New Inbox Message"),
                "text"              => __("You have a new Inbox message."),
                "send_at"           => $send_at,
                "action_value"      => $value_id,
                "type_id"           => $message_push->getMessageType(),
                "app_id"            => $this->getApplication()->getId(),
                "base_url"          => $this->getRequest()->getBaseUrl(),
                "send_to_all"       => 0,
                "send_to_specific_customer" => 1
            );

            # Set the custom push image in case a thumbnail is defined
            if($inbox_message) {
                $thumbnail = $inbox_message->getThumbnail();
                $path = Core_Model_Directory::getBasePathTo("/images/application{$thumbnail}");
                if(is_readable($path)) {
                    $data_push["custom_image"] = $thumbnail;
                }
            }

            $message_push->setData($data_push)->save();

            foreach ($customers as $customer) {
                $customer_message = new Push_Model_Customer_Message();
                $customer_message_data = array(
                    "customer_id" => $customer,
                    "message_id" => $message_push->getId()
                );
                $customer_message->setData($customer_message_data);
                $customer_message->save();
            }

            # Fallback for SAE, or disabled cron
            if(!Cron_Model_Cron::is_active()) {
                $cron = new Cron_Model_Cron();
                $task = $cron->find("pushinstant", "command");
                Siberian_Cache::__clearLocks();
                $siberian_cron = new Siberian_Cron();
                $siberian_cron->execute($task);
            }

        } else {
            log_debug("Inbox_MessageController::_pushToUsers, individual push not available");
        }

    }


}