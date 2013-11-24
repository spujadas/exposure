<?php

/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

namespace Exposure\View;

use Sociable\Model\Location,
    Sociable\Model\Country,
    Exposure\Model\Theme,
    Exposure\ODM\ObjectDocumentMapper;

use \Doctrine\ODM\MongoDB\DocumentManager;

class DisplayHelpers {
    /*************************
       Locations and places
    */

    public static function getDisplayDataForPlace($place, $languageCode) {
        $data = array();
        $currentPlace = $place;
        while (is_a($currentPlace, 'Sociable\Model\Location')) {
            array_unshift($data, $currentPlace->getName());
            $currentPlace = 
                ($currentPlace->getParentType() == Location::PARENT_TYPE_COUNTRY)
                ?$currentPlace->getParentCountry()
                :$currentPlace->getParentLocation();
        }
        array_unshift($data, $currentPlace->getName()->getStringByLanguageCode($languageCode));
        return $data;
    }
    
    /***********
       Themes
    */
    
    public static function getDisplayDataForTheme(Theme $theme, $languageCode) {
        $data = array();
        $currentTheme = $theme;
        while (!is_null($currentTheme)) {
            array_unshift($data, $currentTheme->getName()->getStringByLanguageCode($languageCode));
            $currentTheme = $currentTheme->getParentTheme();
        }
        return $data;
    }
}

