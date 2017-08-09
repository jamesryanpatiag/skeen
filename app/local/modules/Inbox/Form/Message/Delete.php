<?php

/**
 * Class Inbox_Form_Message_Delete
 */
class Inbox_Form_Message_Delete extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/inbox/message/deletepost"))
            ->setAttrib("id", "form-delete-inbox-message")
            ->setConfirmText("You are about to remove this Message ! Are you sure ?");
        ;

        /** Bind as a delete form */
        self::addClass("delete", $this);

        $message_id = $this->addSimpleHidden("message_id");
        $message_id->setMinimalDecorator();

        $customer_id = $this->addSimpleHidden("customer_id");
        $customer_id->setMinimalDecorator();

        $mini_submit = $this->addMiniSubmit();
    }
}