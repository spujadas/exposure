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

use Sociable\Model\MultiLanguageString,
        Sociable\Utility\StringValidator;

class ModerationReason {
    protected $id = null;
    
    protected $code = null;
    const CODE_MAX_LENGTH = 32;
    
    const EXCEPTION_NOT_IN_CATALOGUE = 'not in catalogue';
    
    protected $content = null;
    const CONTENT_MAX_LENGTH = 128;

    public function getId() {
        return $this->id;
    }
    
    public function setCode($code) {
        try {
            self::validateCode($code);
        } catch (Exception $e) {
            $this->code = null;
            throw $e;
        }

        $this->code = $code;
        return $this->code;
    }

    public static function validateCode($code) {
        StringValidator::validate($code, 
                array(
                    'not_empty' => true,
                    'max_length' => self::CODE_MAX_LENGTH));
    }

    public function getCode() {
        return $this->code;
    }

    
    public function setContent(MultiLanguageString $content) {
        try {
            $this->validateContent($content);
        }
        catch (Exception $e) {
            $this->content = null;
            throw $e;
        }
        $this->content = $content;
        return $this->content;
    }
    
    public function getContent() {
        return $this->content;
    }
    
    protected function validateContent (MultiLanguageString $content) {
        $content->validate(array('max_length' => self::CONTENT_MAX_LENGTH));
    }
    
    public function validate() {
        $this->validateCode($this->code);
        $this->validateContent($this->content);
    }
}


