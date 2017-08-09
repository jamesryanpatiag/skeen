<?php

/**
 * Class Inbox_Model_CustomerMessage
 */
class Inbox_Model_CustomerMessage extends Core_Model_Default {

    /**
     * Inbox_Model_CustomerMessage constructor.
     * @param array $params
     */
    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Inbox_Model_Db_Table_CustomerMessage';
        return $this;
    }

    /**
     * @param $value_id
     * @param int $limit
     * @return mixed
     */
    public function findByValueId($value_id, $limit = 5000) {
        return $this->getTable()->findByValueId($value_id, $limit);
    }

    /**
     * @param $value_id
     * @param $customer_id
     * @param $offset
     * @return mixed
     */
    public function findByCustomerId($value_id, $customer_id, $offset, $visibility = "editor") {
        return $this->getTable()->findByCustomerId($value_id, $customer_id, $offset, $visibility);
    }
}