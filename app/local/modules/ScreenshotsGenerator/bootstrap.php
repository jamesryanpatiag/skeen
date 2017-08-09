<?php

/**
 * Class ScreenshotsGenerator_Bootstrap
 */
class ScreenshotsGenerator_Bootstrap {

    public static function init($bootstrap) {
    	
    	
    	//Siberian_Module::addMenu($module, $code, $title, $link);

    	
    	Siberian_Module::addMenu("ScreenshotsGenerator", "screenshotsgenerator", "Screenshots", "screenshotsgenerator/backoffice_view");

    	
    }

}