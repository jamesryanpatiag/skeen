<?php
class Inbox_Model_Application_Customer_Message extends Core_Model_Default
{

    const DISPLAY_PER_PAGE = 10;

    public function __construct($params = array())
    {
        parent::__construct($params);
        $this->_db_table = 'Inbox_Model_Db_Table_Application_Customer_Message';
        return $this;
    }

}