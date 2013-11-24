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

use Sociable\Utility\StringValidator;

class ModerationStatus {
    protected $status = null;
    const STATUS_FIRST_USER_EDIT = 'first user edit';
    const STATUS_USER_EDIT = 'user edit';
    const STATUS_SUBMITTED_FIRST_TIME = 'submitted first time';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_FLAGGED = 'flagged';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /** @var ModerationReason */
    protected $reasonCode = null; // can be null
    
    protected $comment = null; // can be null
    const COMMENT_MAX_LENGTH = 1000;

    const EXCEPTION_INVALID_STATUS = 'invalid status';

    public function setStatus($status) {
        try {
            $this->validateStatus($status);
        }
        catch (Exception $e) {
            $this->status = null;
            throw $e;
        }
        $this->status = $status;
        return $this->status;

    }

    public function getStatus() {
        return $this->status;
    }
    
    protected function validateStatus($status) {
        if (!in_array($status, 
            array(
                self::STATUS_FIRST_USER_EDIT, 
                self::STATUS_USER_EDIT,
                self::STATUS_SUBMITTED_FIRST_TIME, 
                self::STATUS_SUBMITTED, 
                self::STATUS_FLAGGED, 
                self::STATUS_APPROVED, 
                self::STATUS_REJECTED))) {
            throw new ModerationStatusException(self::EXCEPTION_INVALID_STATUS);
        }
    }
    
    public function setComment($comment = null) {
        if (!is_null($comment)) {
            try {
                self::validateComment($comment);
            }
            catch (Exception $e) {
                $this->comment = null;
                throw $e;
            }
        }
        $this->comment = $comment;
        return $this->comment;
    }
    
    protected static function validateComment($comment) {
        StringValidator::validate($comment, 
                array('max_length' => self::COMMENT_MAX_LENGTH)
                );
    }
    
    public function getComment() {
        return $this->comment;
    }

    public function setReasonCode ($code = null) {
        if (!is_null($code)) {
            try {
                $this->validateReasonCode($code);
            }
            catch (Exception $e) {
                $this->reasonCode = null;
                throw $e;
            }
        }
        $this->reasonCode = $code;
        return $this->reasonCode;
    }
    
    protected function validateReasonCode ($code) {
        ModerationReason::validateCode($code);
    }
    
    public function getReasonCode() {
        return $this->reasonCode;
    }
    
    public function validate() {
        $this->validateStatus($this->status);
        if (!is_null($this->reasonCode)) {
            $this->validateReasonCode($this->reasonCode);
        }
        if (!is_null($this->comment)) {
            $this->validateComment($this->comment);
        }
    }
}


