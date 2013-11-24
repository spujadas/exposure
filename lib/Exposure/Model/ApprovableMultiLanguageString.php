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

use Sociable\Model\MultiLanguageString;

class ApprovableMultiLanguageString extends ApprovableContent {
    public function __construct($string = null, $languageCode = null, 
            $constraints = array()) {
        $this->type = parent::TYPE_MULTI_LANGUAGE_STRING;
        if (!is_null($string) && !is_null($languageCode)) {
            $this->setCurrent(new MultiLanguageString($string, $languageCode, 
                    $constraints));
        }
    }
    
    public function setCurrent (MultiLanguageString $string, $constraints = array()) {
        try {
            $this->validateMultiLanguageString($string, $constraints);
        }
        catch (Exception $e) {
            $this->current = null;
            throw $e;
        }
        $this->current = $string;
        return $this->current;
    }
    
    public function validateMultiLanguageString (MultiLanguageString $string, $constraints = array()) {
        $string->validate($constraints);
    }

    public function setPrevious (MultiLanguageString $string = null, $constraints = array()) {
        if (!is_null($string)) {
            try {
                $this->validateMultiLanguageString($string, $constraints);
            }
            catch (Exception $e) {
                $this->previous = null;
                throw $e;
            }
        }
        $this->previous = $string;
        return $this->previous;
    }
    
    public function validate($constraints = array()) {
        parent::validateCommon();
        if (is_null($this->current)) {
            throw new ApprovableContentException(ApprovableContent::EXCEPTION_MISSING_CURRENT);
        }
        $this->validateMultiLanguageString($this->current, $constraints);
        if (!is_null($this->previous)) {
            $this->validateMultiLanguageString($this->previous, $constraints);
        }
    }
}


