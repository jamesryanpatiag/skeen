<?php

/**
 * Class Inbox_CustomermessageController
 */
class Inbox_CustomermessageController extends Application_Controller_Default {

    /**
     * Load form edit
     */
    public function loadformAction() {
        $customer_message_id = $this->getRequest()->getParam("customer_message_id");

        $inbox_customer_message = new Inbox_Model_CustomerMessage();
        $inbox_customer_message->find($customer_message_id);
        if($inbox_customer_message->getId()) {
            $form = new Inbox_Form_CustomerMessage();

            $form->populate($inbox_customer_message->getData());
            $form->setValueId($this->getCurrentOptionValue()->getId());
            $form->removeNav("nav-customer-message");
            $form->addNav("edit-nav-customer-message", "Save", false);
            $form->setCustomerMessageId($inbox_customer_message->getId());

            $data = array(
                "success"   => 1,
                "form"      => $form->render(),
                "message"   => __("Success."),
            );
        } else {
            /** Do whatever you need when form is not valid */
            $data = array(
                "error"     => 1,
                "message"   => __("The Customer Message you are trying to edit doesn't exists."),
            );
        }

        $this->_sendJson($data);
    }

    /**
     * Create/Edit Customer Message
     *
     * @throws exception
     */
    public function editpostAction() {
        $values = $this->getRequest()->getPost();

        $form = new Inbox_Form_CustomerMessage();
        if($form->isValid($values)) {
            /** Do whatever you need when form is valid */
            $inbox_customer_message = new Inbox_Model_CustomerMessage();
            $inbox_customer_message->addData($values);
            $inbox_customer_message->save();

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
     * Delete Customer Message
     */
    public function deletepostAction() {
        $values = $this->getRequest()->getPost();

        $form = new Inbox_Form_CustomerMessage_Delete();
        if($form->isValid($values)) {
            $inbox_customer_message = new Inbox_Model_CustomerMessage();
            $inbox_customer_message->find($values["customer_message_id"]);
            $inbox_customer_message->delete();

            $data = array(
                "success"           => 1,
                "success_message"   => __("Customer Message successfully deleted."),
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


}