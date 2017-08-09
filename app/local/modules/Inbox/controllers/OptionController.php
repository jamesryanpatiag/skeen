<?php

/**
 * Class Inbox_OptionController
 */
class Inbox_OptionController extends Application_Controller_Default {

    /**
     * Create/Edit Option
     *
     * @throws exception
     */
    public function editpostAction() {
        $values = $this->getRequest()->getPost();

        $form = new Inbox_Form_Option();
        if($form->isValid($values)) {
            /** Do whatever you need when form is valid */
            $inbox_option = new Inbox_Model_Option();
            $inbox_option->find($values["inbox_option_id"]);
            $inbox_option->addData($values);
            $inbox_option->save();

            $data = array(
                "success" => 1,
                "message" => __("Success."),
            );
        } else {
            /** Do whatever you need when form is not valid */
            $data = array(
                "error"     => 1,
                "message"   => $form->getTextErrors(),
                "errors"    => $form->getTextErrors(true),
            );
        }

        $this->_sendJson($data);
    }

}