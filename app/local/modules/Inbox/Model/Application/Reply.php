<?php
class Inbox_Model_Application_Reply extends Core_Model_Default
{

    const DISPLAYED_PER_PAGE = 10;

    public function __construct($params = array())
    {
        parent::__construct($params);
        $this->_db_table = 'Inbox_Model_Db_Table_Application_Reply';
        return $this;
    }

    public function findAll($parent_id, $customer_id = null, $all = true, $offset = 0) {
        return $this->getTable()->findAll($parent_id, $customer_id, $all, $offset);
    }

    public function getNumberOfComments($message_id) {
        return $this->getTable()->getNumberOfComments($message_id);
    }
}