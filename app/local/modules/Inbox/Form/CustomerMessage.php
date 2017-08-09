<?php

/**
 * Class Inbox_Form_CustomerMessage
 */
class Inbox_Form_CustomerMessage extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/inbox/customermessage/editpost"))
            ->setAttrib("id", "form-inbox-customer-message")
            ->addNav("nav-inbox-customer-message")
        ;

        /** Bind as a create form */
        self::addClass("create", $this);

        $customer_message_id = $this->addSimpleHidden("customer_message_id");

        /** Builds the default form from schema */
        
        $message_id = $this->addSimpleText("message_id", __("Message Id"));
        $message_id->setRequired(true);


        $customer_id = $this->addSimpleText("customer_id", __("Customer Id"));
        $customer_id->setRequired(true);


    }

    /**
     * @param $customer_message_id
     */
    public function setCustomerMessageId($customer_message_id) {
        $this->getElement("customer_message_id")->setValue($customer_message_id)->setRequired(true);
    }
}