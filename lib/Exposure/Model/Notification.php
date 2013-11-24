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

abstract class Notification {
    protected $id = null;
    
    protected $status = null;
    const STATUS_READ = 'read';
    const STATUS_UNREAD = 'unread';
    const STATUS_ARCHIVED = 'archived';
    const EXCEPTION_INVALID_STATUS = 'invalid status';

    protected $type = null;
    const TYPE_PROJECT_THEME_SUGGESTION = 'project theme suggestion';
    const TYPE_PROJECT_WANT = 'project want';
    const TYPE_PROJECT_MODERATION = 'project moderation';
    const TYPE_PROFILE_MODERATION = 'profile moderation';
    const TYPE_SPONSOR_CONTRIBUTION = 'sponsor contribution';
    const TYPE_SPONSOR_RETURN = 'sponsor return';
    const TYPE_COMMENT = 'comment';
    const EXCEPTION_INVALID_TYPE = 'invalid type';
    const EXCEPTION_TYPE_MISMATCH = 'type mismatch';

    protected $event = null;
    const EXCEPTION_INVALID_EVENT = 'invalid event';
    
    /** @var \DateTime */
    protected $dateTime = null;
    const EXCEPTION_INVALID_DATE_TIME = 'invalid date time';
    
    protected $content = null;
    const CONTENT_MAX_LENGTH = 500;

    public function getId() {
        return $this->id;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        try {
            $this->validateStatus($status);
        } catch (Exception $e) {
            $this->status = null;
            throw $e;
        }
        $this->status = $status;
        return $this->status;
    }
    
    protected function validateStatus($status) {
        if (($status != self::STATUS_ARCHIVED) 
                && ($status != self::STATUS_READ)
                && ($status != self::STATUS_UNREAD)) {
            throw new NotificationException(self::EXCEPTION_INVALID_STATUS);
        }
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
        if (!in_array($type, array(
            self::TYPE_COMMENT, 
            self::TYPE_PROFILE_MODERATION,
            self::TYPE_PROJECT_MODERATION,
            self::TYPE_PROJECT_THEME_SUGGESTION,
            self::TYPE_PROJECT_WANT,
            self::TYPE_SPONSOR_CONTRIBUTION,
            self::TYPE_SPONSOR_RETURN))) {
            throw new NotificationException(self::EXCEPTION_INVALID_TYPE);
        }
    }

    public function setEvent($event) {
        try {
            $this->validateEvent($event);
        } catch (Exception $e) {
            $this->event = null;
            throw $e;
        }
        $this->event = $event;
        return $this->event;
    }
    
    public function getEvent() {
        return $this->event;
    }
    
    abstract protected function validateEvent($event);

    public function getDateTime() {
        return $this->dateTime;
    }
            
    public function setDateTime(\DateTime $datetime) {
        $this->dateTime = $datetime;
        return $this->dateTime;
    }
    
    public function getContent() {
        return $this->content;
    }

    public function setContent($content = null) {
        if (!is_null($content)) {
            try {
                $this->validateContent($content);
            } catch (Exception $e) {
                $this->content = null;
                throw $e;
            }
        }
        $this->content = $content;
        return $this->content;
    }
    
    protected function validateContent($content) {
        StringValidator::validate($content, array(
            'not_empty' => true,
            'max_length' => self::CONTENT_MAX_LENGTH
        ));
    }
    
    public function validate() {
        $this->validateStatus($this->status);
        $this->validateType($this->type);
        if (!is_a($this->dateTime, 'DateTime')) {
            throw new NotificationException(self::EXCEPTION_INVALID_DATE_TIME);
        }
        if (!is_null($this->content)) {
            $this->validateContent($this->content);
        }
    }

    abstract public function isStatusEditableByUser(User $user);
}


