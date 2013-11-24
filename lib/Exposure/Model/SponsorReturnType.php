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

use Sociable\Utility\StringValidator,
    Sociable\Model\MultiLanguageString;

class SponsorReturnType {
    protected $id = null;
    
    protected $label;
    const LABEL_MAX_LENGTH = 32;
    
    /** @var MultiLanguageString */
    protected $description = null;
    const DESCRIPTION_MAX_LENGTH = 250;

    public function getId() {
        return $this->id; 
    }
    
    public function setLabel($label) {
        try {
            $this->validateLabel($label);
        } catch (Exception $e) {
            $this->label = null;
            throw $e;
        }

        $this->label = $label;
        return $this->label;
    }

    protected function validateLabel($label) {
        StringValidator::validate($label, array(
            'max_length' => self::LABEL_MAX_LENGTH,
            'not_empty' => true)
        );
    }

    public function getLabel() {
        return $this->label;
    }
    
    public function setDescription(MultiLanguageString $description) {
        try {
            $this->validateDescription($description);
        } catch (Exception $e) {
            $this->description = null;
            throw $e;
        }
        $this->description = $description;
        return $this->description;
        
    }
    
    protected function validateDescription(MultiLanguageString $description) {
        $description->validate(array(
            'not_empty' => true,
            'max_length' => self::DESCRIPTION_MAX_LENGTH));
    }

    public function getDescription() {
        return $this->description;
    }
    
    public function validate() {
        $this->validateLabel($this->label);
        $this->validateDescription($this->description);
    }
}


