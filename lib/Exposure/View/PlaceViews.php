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

use Sociable\Common\Configuration,
    Sociable\Model\Location,
    Sociable\Model\Country,
    Exposure\ODM\ObjectDocumentMapper;

use \Doctrine\ODM\MongoDB\DocumentManager;

class PlaceViews extends View {

    /***********
        Place
    */
    
    protected function placePreRoute() {
        if ((count($this->request) != 3)
            || (($this->request[1] != 'country') && ($this->request[1] != 'location'))) {
            return self::INVALID_PARAMS;
        }
    }

    public function place() {
        if ($preRouting = $this->placePreRoute()) {
            return $preRouting;
        }

        if (($this->request[1]) == 'country') {
            $placeData = FormHelpers::getFormDataForCountryCode(
                $this->config->getDocumentManager(),
                $this->request[2]);
        }
        else {
            $placeData = FormHelpers::getFormDataForLocationLabel(
                $this->config->getDocumentManager(),
                $this->request[2]);
        }
        $this->loadTemplate('location.inc.tpl.html');
        $this->displayTemplate(array(
            'locationtree' => $placeData,
        ));
    }
}

