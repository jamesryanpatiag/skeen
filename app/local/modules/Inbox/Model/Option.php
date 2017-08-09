<?php

/**
 * Class Inbox_Model_Reply
 */
class Inbox_Model_Option extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Inbox_Model_Db_Table_Option';
        return $this;
    }

    /**
     * @param $option_value
     * @return $this
     */
    public function prepareFeature($option_value) {

        $option_model = new Inbox_Model_Option();
        $option = $option_model->find($option_value->getId(), "value_id");

        if (!$option->getId()) {
            $option =  new Inbox_Model_Option();
            $option->save();
        }

        return $this;
    }
}