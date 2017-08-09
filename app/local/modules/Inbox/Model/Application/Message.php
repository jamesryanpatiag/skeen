<?php
class Inbox_Model_Application_Message extends Core_Model_Default
{

    const DISPLAYED_PER_PAGE = 10;

    public function __construct($params = array())
    {
        parent::__construct($params);
        $this->_db_table = 'Inbox_Model_Db_Table_Application_Message';
        return $this;
    }

    /**
     * @return array
     */
    public function getInappStates($value_id) {

        $in_app_states = array(
            array(
                "state" => "inbox-list",
                "offline" => false,
                "params" => array(
                    "value_id" => $value_id,
                ),
            ),
        );

        return $in_app_states;
    }

    public function getNumberOfComments($message_id = null) {
        $message_id = $message_id?$message_id:$this->getId();
        $reply = new Inbox_Model_Application_Reply();
        return $reply->getNumberOfComments($message_id);
    }

    public function findAllByCustomer($value_id, $customer_id, $offset = 0) {
        return $this->getTable()->findAllByCustomer($value_id, $customer_id, $offset);
    }

    public function copyTo($option) {

        $this->setId(null)
            ->setValueId($option->getId())
        ;

        $this->save();

        return $this;

    }
}