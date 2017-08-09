<?php

/**
 * Class Inbox_Model_Reply
 */
class Inbox_Model_Reply extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Inbox_Model_Db_Table_Reply';
        return $this;
    }

    /**
     * @param $message_id
     * @param $customer_id
     * @param bool $all
     * @param int $offset
     * @param bool $limit
     * @return mixed
     */
    public function findByMessageId($message_id, $customer_id, $all = true, $offset = 0, $limit = false) {
        return $this->getTable()->findByMessageId($message_id, $customer_id, $all, $offset, $limit);
    }
}