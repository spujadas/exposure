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

use Sociable\Model\LabelledImage;

class ApprovableLabelledImage extends ApprovableContent {
    protected $id = null;
    
    public function __construct() {
        $this->type = parent::TYPE_LABELLED_IMAGE;
    }

    public function getId() {
        return $this->id;
    }
    
    public function setCurrent (LabelledImage $image) {
        try {
            $this->validateImage($image);
        }
        catch (Exception $e) {
            $this->current = null;
            throw $e;
        }
        $this->current = $image;
        return $this->current;
    }
    
    public function validateImage (LabelledImage $image) {
        $image->validate();
    }

    public function setPrevious (LabelledImage $image = null) {
        if (!is_null($image)) {
            try {
                $this->validateImage($image);
            }
            catch (Exception $e) {
                $this->previous = null;
                throw $e;
            }
        }
        $this->previous = $image;
        return $this->previous;
    }
    
    public function validate() {
        parent::validateCommon();
        if (is_null($this->current)) {
            throw new ApprovableContentException(ApprovableContent::EXCEPTION_MISSING_CURRENT);
        }
        $this->validateImage($this->current);
        if (!is_null($this->previous)) {
            $this->validateImage($this->previous);
        }
    }
}


