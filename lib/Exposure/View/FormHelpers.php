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
    Exposure\ODM\ObjectDocumentMapper;

use \Doctrine\ODM\MongoDB\DocumentManager;

class FormHelpers {
    /*************************
       Locations and places
    */
    
    public static function getFormDataForLocation(Location $location) {
        $data = array();

        // get parent locations data
        $locationFormName = 'location';
        $currentPlace = $location;
        $i = 1;
        while (is_a($currentPlace, 'Sociable\Model\Location')) {
            $locationTreeItem = array();
            $locationTreeItem['current'] = $currentPlace->getLabel();
            $locations = $currentPlace->getSiblings();
            $locationTreeItem['locations'] = $locations;
            $locationTreeItem['name'] = $currentPlace->getFamilyName();
            $locationTreeItem['formName'] = $locationFormName;
            array_unshift($data, $locationTreeItem);
            if ($currentPlace->getParentType() == Location::PARENT_TYPE_COUNTRY) {
                break;
            }
            else {
                $currentPlace = $currentPlace->getParentLocation();
                $locationFormName = 'location' . $i;
                $i++;
            }
        }

        // get sublocations data
		$sublocations = $location->getSublocations(); 
        if ($sublocations->count()) {
            $locationTreeItem = array();
            $locationTreeItem['locations'] = $sublocations;
            $locationTreeItem['name'] = $location->getSublocationsName();
            $locationTreeItem['formName'] = 'sublocation';
            $locationTreeItem['current'] = '';
            $data[] = $locationTreeItem;
        }

        return $data;
    }

    public static function getFormDataForCountry(Country $country) {
        $data = array();

        // get locations data
        $locations = $country->getLocations(); 
        if ($locations->count()) {
            $locationTreeItem = array();
            $locationTreeItem['locations'] = $locations;
            $locationTreeItem['name'] = $country->getLocationsName();
            $locationTreeItem['formName'] = 'location';
            $data[] = $locationTreeItem;
        }
        return $data;
    }

    public static function getFormDataForPlace($place) {

        // country
        if (is_a($place, 'Sociable\Model\Country')) {
            return self::getFormDataForCountry($place);
        }

        // location
        if (is_a($place, 'Sociable\Model\Location')) {
            return self::getFormDataForLocation($place);
        }

        // junk
        return array();
    }

    public static function getFormDataForLocationLabel(DocumentManager $dm, $locationLabel) {
        return self::getFormDataForLocation(
            ObjectDocumentMapper::getByLabel($dm, 'Sociable\Model\Location', $locationLabel));
    }

    public static function getFormDataForCountryCode(DocumentManager $dm, $countryCode) {
        return self::getFormDataForCountry(
            ObjectDocumentMapper::getByCode($dm, 'Sociable\Model\Country', $countryCode));
    }

    /***********
       Themes
    */
    
    public static function getFormDataForThemeLabel(DocumentManager $dm, $themeLabel) {
        return self::getFormDataForTheme($dm, ObjectDocumentMapper::getByLabel($dm, 'Exposure\Model\Theme', $themeLabel));
    }

    public static function getFormDataForRootTheme(DocumentManager $dm) {
        $data = array();
        $themeTreeItem = array();
        $themes = ObjectDocumentMapper::getRootThemes($dm);
        $themeTreeItem['themes'] = $themes;
        $themeTreeItem['name'] = 'Theme';
        $themeTreeItem['formName'] = 'theme';
        $themeTreeItem['current'] = '';
        $data[] = $themeTreeItem;
        return $data;
    }

    public static function getFormDataForTheme(DocumentManager $dm, $theme) {
        $data = array();

        // null (root)
        if (is_null($theme)) {
            return self::getFormDataForRootTheme($dm);
        }

        // theme
        if (is_a($theme, 'Exposure\Model\Theme')) {
            $currentTheme = $theme;

            // get parent themes data
            $themeFormName = 'theme';
            $i = 1;
            while (is_a($currentTheme, 'Exposure\Model\Theme')) {
                $themeTreeItem = array();
                $themeTreeItem['current'] = $currentTheme->getLabel();
                // root theme
                if (is_null($currentTheme->getParentTheme())) {
                    $themes = ObjectDocumentMapper::getRootThemes($dm);
                }
                // non-root theme
                else {
                    $themes = $currentTheme->getParentTheme()->getChildrenThemes();
                }
                $themeTreeItem['themes'] = $themes;
                $themeTreeItem['formName'] = $themeFormName;
                if (is_null($currentTheme->getParentTheme())) {
                    $themeTreeItem['name'] = 'Theme';
                    array_unshift($data, $themeTreeItem);
                    break;
                }
                else {
                    $themeTreeItem['name'] = 'Sub-theme';
                    array_unshift($data, $themeTreeItem);
                    $currentTheme = $currentTheme->getParentTheme();
                    $themeFormName = 'theme' . $i;
                    $i++;
                }
            }

            // get children theme data
            $childrenThemes = $theme->getChildrenThemes(); 
            if ($childrenThemes->count()) {
                $themeTreeItem = array();
                $themeTreeItem['themes'] = $childrenThemes;
                $themeTreeItem['formName'] = 'subtheme';
                $themeTreeItem['name'] = 'Sub-theme';
                $themeTreeItem['current'] = '';
                $data[] = $themeTreeItem;
            }

            return $data;
        }

        // junk
        return $data;
    }
}

