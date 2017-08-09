<?php

class ScreenshotsGenerator_Backoffice_ViewController extends Backoffice_Controller_Default
{

    public function loadAction() {

        $html = array(
            "title" => $this->_("Screenshots Generator"),
            "icon" => "fa-mobile",
        );

        $this->_sendHtml($html);

    }

    

}

