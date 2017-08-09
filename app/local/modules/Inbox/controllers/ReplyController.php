<?php

/**
 * Class Inbox_ReplyController
 */
class Inbox_ReplyController extends Application_Controller_Default {

    /**
     * Load form edit
     */
    public function loadformAction() {
        $reply_id = $this->getRequest()->getParam("reply_id");

        $inbox_reply = new Inbox_Model_Reply();
        if($inbox_reply->getId()) {
            $form = new Inbox_Form_Reply();

            $form->populate($inbox_reply->getData());
            $form->setValueId($this->getCurrentOptionValue()->getId());
            $form->removeNav("nav-reply");
            $form->addNav("edit-nav-reply", "Save", false);
            $form->setReplyId($inbox_reply->getId());

            $data = array(
                "success"   => 1,
                "form"      => $form->render(),
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
     * Create/Edit Reply
     *
     * @throws exception
     */
    public function editpostAction() {
        $values = $this->getRequest()->getPost();
        $option_value = $this->getCurrentOptionValue();
        $value_id = $option_value->getId();

        $form = new Inbox_Form_Reply();
        if($form->isValid($values)) {
            /** Do whatever you need when form is valid */
            $inbox_reply = new Inbox_Model_Reply();
            $inbox_reply->addData($values);
            $inbox_reply->save();

            $inbox_message_model = new Inbox_Model_Message();
            $inbox_message = $inbox_message_model->find($values["parent_id"]);

            $inbox_customer_message_model = new Inbox_Model_CustomerMessage();
            $inbox_customer_message = $inbox_customer_message_model->find(array(
                "message_id" => $values["parent_id"],
                "customer_id" => $values["customer_id"]
            ));

            # Set the thread as new.
            $inbox_customer_message
                ->setHasNewReply(true)
                ->save();

            if($values["send_push"] == "1") {
                $this->_pushToUser($value_id, null, $values["customer_id"], $inbox_message);
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
     * Delete Reply
     */
    public function deletepostAction() {
        $values = $this->getRequest()->getPost();

        $form = new Inbox_Form_Reply_Delete();
        if($form->isValid($values)) {
            $inbox_reply = new Inbox_Model_Reply();
            $inbox_reply->find($values["reply_id"]);
            $inbox_reply->delete();

            $data = array(
                "success"           => 1,
                "success_message"   => __("Reply successfully deleted."),
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
     * @param $value_id
     * @param null $send_at
     * @param array $customers
     */
    protected function _pushToUser($value_id, $send_at = null, $customer = array(), $inbox_message = null) {

        if (Push_Model_Message::hasIndividualPush()) {
            $message_push = new Push_Model_Message();
            $message_push->setMessageType(Push_Model_Message::TYPE_PUSH);

            $data_push = array(
                "title"             => __("New Inbox reply"),
                "text"              => __("You have a new Inbox reply."),
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

            $customer_message = new Push_Model_Customer_Message();
            $customer_message_data = array(
                "customer_id" => $customer,
                "message_id" => $message_push->getId()
            );
            $customer_message->setData($customer_message_data);
            $customer_message->save();

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