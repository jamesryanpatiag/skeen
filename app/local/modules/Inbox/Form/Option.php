<?php

/**
 * Class Inbox_Form_Option
 */
class Inbox_Form_Option extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/inbox/option/editpost"))
            ->setAttrib("id", "form-inbox-option")
        ;

        /** Bind as a create form */
        self::addClass("onchange", $this);

        $inbox_option_id = $this->addSimpleHidden("inbox_option_id");

        /** Builds the default form from schema */
        $value_id = $this->addSimpleHidden("value_id", __("Value Id"));


        $recipient_email = $this->addSimpleText("recipient_email", __("Global recipient e-mail"));

        $send_email_to = $this->addSimpleSelect("send_email_to", __("Send answer e-mail to"), array(
            "all"           => __("Global recipient & Admin who created the message"),
            "recipient"     => __("Only to global recipient"),
            "admin"         => __("Only to Admin"),
            "disabled"      => __("Disable answer e-mails"),
        ));
        $send_email_to->setRequired(true);

        $message_limit = $this->addSimpleNumber("message_limit", __("New message limit"), 1, 100000, true, 1);

        $display_type = $this->addSimpleSelect("display_type", __("Display type"), array(
            "default"           => __("List"),
            "compact"           => __("Compact, list"),
            "card"              => __("Card"),
            "compact-card"      => __("Compact, card"),
        ));

        $answer_from = $this->addSimpleSelect("answer_from", __("Display answers from"), array(
            "appname"   => __("Application name"),
            "admin"     => __("Admin who answered"),
        ));

    }

    /**
     * @param $inbox_option_id
     */
    public function setInboxOptionId($inbox_option_id) {
        $this->getElement("inbox_option_id")->setValue($inbox_option_id)->setRequired(true);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function isValid($data) {
        $emails = explode(";", str_replace(",", ";", $data["recipient_email"]));
        $errors = array();
        foreach($emails as $email) {
            if (empty($email) || !Zend_Validate::is($email, "EmailAddress")) {
                $errors[] = __("Invalid e-mail address '%s'", $email);
            }
        }

        if(!empty($errors)) {
            $this->getElement("recipient_email")->addErrors($errors);
        }

        return parent::isValid($data);
    }
}