<?php

/**
 * Class Inbox_Form_Reply_Delete
 */
class Inbox_Form_Reply_Delete extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/inbox/reply/deletepost"))
            ->setAttrib("id", "form-delete-inbox-reply")
            ->setConfirmText("You are about to remove this Reply ! Are you sure ?");
        ;

        /** Bind as a delete form */
        self::addClass("delete", $this);

        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
            ->from('inbox_reply')
            ->where('inbox_reply.reply_id = :value')
        ;

        $reply_id = $this->addSimpleHidden("reply_id", __("Reply"));
        $reply_id->addValidator("Db_RecordExists", true, $select);
        $reply_id->setMinimalDecorator();

        $mini_submit = $this->addMiniSubmit();
    }
}