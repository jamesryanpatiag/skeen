<?php

/**
 * Class Inbox_Model_Message
 */
class Inbox_Model_Message extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Inbox_Model_Db_Table_Message';
        return $this;
    }
}