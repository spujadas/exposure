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
    Exposure\Model\ModerationStatus;

abstract class ApprovableContent {
    /** @var ModerationStatus */
    protected $moderationStatus = null;
    const EXCEPTION_MISSING_STATUS = 'missing moderation status';

    protected $current = null;
    protected $previous = null;
    
    protected $type = null;
    const TYPE_MULTI_LANGUAGE_STRING = 'multi language string';
    const TYPE_LABELLED_IMAGE = 'labelled image';
    const EXCEPTION_INVALID_TYPE = 'invalid type';

    const EXCEPTION_MISSING_CURRENT = 'missing current';
    
    public function getModerationStatus() {
        return $this->moderationStatus;
    }

    public function setModerationStatus(ModerationStatus $moderationStatus) {
        try {
            $this->validateModerationStatus($moderationStatus);
        } catch (Exception $e) {
            $this->moderationStatus = null;
            throw $e;
        }
        $this->moderationStatus = $moderationStatus;
        return $this->moderationStatus;
    }
    
    protected function validateModerationStatus(ModerationStatus $moderationStatus) {
        $moderationStatus->validate();
    }

    public function getCurrent () {
        return $this->current;
    }

    public function getPrevious () {
        return $this->previous;
    }

    public function getLatestApproved() {
        if (!is_null($this->moderationStatus) 
            && ($this->moderationStatus->getStatus() == ModerationStatus::STATUS_APPROVED)) {
            return $this->current;
        }
        return $this->previous;
    }

    public function getType() {
        return $this->type;
    }

    protected function setType($type) {
        try {
            $this->validateType($type);
        } catch (Exception $e) {
            $this->type = null;
            throw $e;
        }
        $this->type = $type;
        return $this->type;
    }
    
    protected function validateType($type) {
        if (($type != self::TYPE_LABELLED_IMAGE) 
                && ($type != self::TYPE_MULTI_LANGUAGE_STRING)) {
            throw new ApprovableContentException(self::EXCEPTION_INVALID_TYPE);
        }
    }
    
    public function validateCommon() {
        if (is_null($this->moderationStatus)) {
            throw new ApprovableContentException(self::EXCEPTION_MISSING_STATUS);
        }
        $this->validateModerationStatus($this->moderationStatus);
        $this->validateType($this->type);
    }
}


