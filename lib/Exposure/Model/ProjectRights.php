<?php

/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

namespace Exposure\Model;

use Sociable\Utility\NumberValidator;

class ProjectRights {
    protected $displayDescription = false;
    protected $numberDisplayedPhotos = 1;
    const NUMBER_DISPLAYED_PHOTOS_MAX = 10;
    protected $displayWebPresence = false;
    
    public function getDisplayDescription() {
        return $this->displayDescription;
    }

    public function setDisplayDescription($displayDescription) {
        $this->displayDescription = (bool) $displayDescription;
        return $this->displayDescription;
    }

    public function getNumberDisplayedPhotos() {
        return $this->numberDisplayedPhotos;
    }

    public function setNumberDisplayedPhotos($numberDisplayedPhotos) {
        if (!is_null($numberDisplayedPhotos)) {
            try {
                $this->validateNumberDisplayedPhotos($numberDisplayedPhotos);
            } catch (\Exception $e) {
                $this->numberDisplayedPhotos = 1;
                throw $e;
            }
        }
        $this->numberDisplayedPhotos = $numberDisplayedPhotos;
        return $this->numberDisplayedPhotos;
    }
    
    protected function validateNumberDisplayedPhotos($numberDisplayedPhotos) {
        NumberValidator::validate($numberDisplayedPhotos, array(
            'int' => true,
            'positive' => true,
            'max' => self::NUMBER_DISPLAYED_PHOTOS_MAX
        ));
    }

    public function getDisplayWebPresence() {
        return $this->displayWebPresence;
    }

    public function setDisplayWebPresence($displayWebPresence) {
        $this->displayWebPresence = (bool) $displayWebPresence;
        return $this->displayWebPresence;
    }
}


