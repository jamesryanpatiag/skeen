<?php

class Inbox_Model_Db_Table_Application_Message extends Core_Model_Db_Table {

    protected $_name = "inbox_message";
    protected $_primary = "message_id";

    public function findAllByCustomer($value_id, $customer_id, $offset) {

        $select = $this->select()
            ->from(array('im' => $this->_name))
            ->joinLeft(array('icm' => 'inbox_customer_message'), 'im.message_id = icm.message_id')
            ->where('im.value_id = ?', $value_id)
            ->where('im.is_visible_in_mobile = 1')
            ->where('im.send_at IS NULL OR im.send_at <= ?', Zend_Date::now()->toString('y-MM-dd HH:mm:ss'))
            ->where('icm.customer_id = ?', $customer_id)
            ->limit(Inbox_Model_Application_Message::DISPLAYED_PER_PAGE, $offset)
            ->order('im.message_id DESC')
            ->setIntegrityCheck(false)
        ;

        return $this->fetchAll($select);

    }
}