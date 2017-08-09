<?php

/**
 * Class Inbox_Form_Reply
 */
class Inbox_Form_Reply extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/inbox/reply/editpost"))
            ->setAttrib("id", "form-inbox-reply")
        ;

        /** Bind as a create form */
        self::addClass("create", $this);

        $reply_id = $this->addSimpleHidden("reply_id");
        $value_id = $this->addSimpleHidden("value_id");
        $customer_id = $this->addSimpleHidden("customer_id");

        /** Builds the default form from schema */
        $parent_id = $this->addSimpleHidden("parent_id");
        $parent_id->setRequired(true);

        $admin_id = $this->addSimpleHidden("admin_id");

        $is_visible_in_editor = $this->addSimpleHidden("is_visible_in_editor");

        $message = $this->addSimpleTextarea("message", __("Message"), false, array("ckeditor" => "cms"));
        $message->setRichtext();
        $message->setRequired(true);

        if(Push_Model_Message::hasIndividualPush()) {
            $send_push = $this->addSimpleCheckbox("send_push", __("Send a push notification"));
        }

        $this->addNav("reply", __("Reply"), false);
    }

    /**
     * @param $reply_id
     */
    public function setReplyId($reply_id) {
        $this->getElement("reply_id")->setValue($reply_id)->setRequired(true);
    }
}