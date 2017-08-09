<?php

/**
 * Class Inbox_Form_CustomerMessage_Delete
 */
class Inbox_Form_CustomerMessage_Delete extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/inbox/customermessage/deletepost"))
            ->setAttrib("id", "form-delete-inbox-customer-message")
            ->setConfirmText("You are about to remove this Customer Message ! Are you sure ?");
        ;

        /** Bind as a delete form */
        self::addClass("delete", $this);

        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
            ->from('inbox_customer_message')
            ->where('inbox_customer_message.customer_message_id = :value')
        ;

        $customer_message_id = $this->addSimpleHidden("customer_message_id", __("CustomerMessage"));
        $customer_message_id->addValidator("Db_RecordExists", true, $select);
        $customer_message_id->setMinimalDecorator();

        $mini_submit = $this->addMiniSubmit();
    }
}