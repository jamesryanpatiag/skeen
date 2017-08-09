<?php

/**
 * Class Inbox_Form_Message
 */
class Inbox_Form_Message extends Cms_Form_Base {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/inbox/message/editpost"))
            ->setAttrib("id", "form-inbox-message")
            ->addNav("nav-inbox-message")
        ;




        /** Builds the default form from schema */
        $title = $this->addSimpleText("title", __("Title"));
        $title->setRequired(true);

        $thumbnail = $this->addSimpleImage("thumbnail", __("Custom thumbnail"), __("Custom thumbnail"), array(
            "width" => 128,
            "height" => 128,
        ));

        $nav_group = $this->addNav("nav-cms", __("Save"), false);

        $this->addSections();

        $nav_group->addElement($this->getElement("sections_html"));

        $value_id = $this->addSimpleHidden("value_id");
        $value_id->setRequired(true);

        $this->addNav("nav-inbox-message-repeat", __("OK"), false);
    }

    /**
     * @param $message_id
     */
    public function setMessageId($message_id) {
        $this->getElement("message_id")->setValue($message_id)->setRequired(true);
    }
}