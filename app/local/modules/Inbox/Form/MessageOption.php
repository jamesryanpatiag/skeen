<?php

/**
 * Class Inbox_Form_MessageOption
 */
class Inbox_Form_MessageOption extends Siberian_Form_Abstract  {

    public function init() {
        parent::init();

        $this->setAttrib("id", "form-inbox-message-option");

        /** Builds the default form from schema */
        $send_at = $this->addSimpleDatetimepicker("send_at", __("Send my message at this date (leave empty for instant sending):"),
            false, self::DATETIMEPICKER);

        $send_to_all = $this->addSimpleCheckbox("send_to_all", __("Send to all"));

        $send_notif = $this->addSimpleCheckbox("send_notif", __("Send a push notification to inform users"));

        $this->addNav("nav-inbox-message-submit", __("Send message"), false);
    }
}