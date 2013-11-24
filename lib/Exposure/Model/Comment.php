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

use Sociable\Utility\NumberValidator,
    Sociable\Utility\StringValidator;

abstract class Comment {
    protected $id = null;
    
    protected $status = null;
    const STATUS_PUBLISHED = 'published';
    const STATUS_FLAGGED = 'flagged';
    const EXCEPTION_INVALID_STATUS = 'invalid status';

    protected $type = null;
    const TYPE_COMMENT_ON_SPONSOR_RETURN = 'sponsor return';
    const TYPE_COMMENT_ON_PROJECT_OWNER = 'project owner';
    const TYPE_COMMENT_ON_SPONSOR_ORGANISATION = 'sponsor organisation';
    const TYPE_COMMENT_ON_PROJECT = 'project';
    const EXCEPTION_INVALID_TYPE = 'invalid type';
    const EXCEPTION_TYPE_MISMATCH = 'type mismatch';
    
    /** @var \DateTime */
    protected $dateTime = null;
    const EXCEPTION_INVALID_DATE_TIME = 'invalid date time';
    
    /** @var User */
    protected $from = null;
    const EXCEPTION_INVALID_FROM = 'invalid from';
    
    protected $rating = null;
    const RATING_MIN = 0;
    const RATING_MAX = 5;
    
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
        if (($status != self::STATUS_FLAGGED) 
                && ($status != self::STATUS_PUBLISHED)) {
            throw new CommentException(self::EXCEPTION_INVALID_STATUS);
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
        if (($type != self::TYPE_COMMENT_ON_PROJECT) 
                && ($type != self::TYPE_COMMENT_ON_PROJECT_OWNER)
                && ($type != self::TYPE_COMMENT_ON_SPONSOR_ORGANISATION)
                && ($type != self::TYPE_COMMENT_ON_SPONSOR_RETURN)) {
            throw new CommentException(self::EXCEPTION_INVALID_TYPE);
        }
    }
    
    public function getDateTime() {
        return $this->dateTime;
    }
            
    public function setDateTime(\DateTime $datetime) {
        $this->dateTime = $datetime;
        return $this->dateTime;
    }

    public function getFrom() {
        return $this->from;
    }

    public function setFrom(User $from) {
        $this->from = $from;
        return $this->from;
    }
    
    public function getRating() {
        return $this->rating;
    }

    public function setRating($rating = null) {
        if (!is_null($rating)) {
            try {
                $this->validateRating($rating);
            } catch (Exception $e) {
                $this->rating = null;
                throw $e;
            }
        }
        $this->rating = $rating;
        return $this->rating;
    }

    protected function validateRating($rating) {
        NumberValidator::validate($rating, array(
            'int' => true,
            'min' => self::RATING_MIN,
            'max' => self::RATING_MAX
        ));
    }

    public function getContent() {
        return $this->content;
    }

    public function setContent($content) {
        try {
            $this->validateContent($content);
        } catch (Exception $e) {
            $this->content = null;
            throw $e;
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
            throw new CommentException(self::EXCEPTION_INVALID_DATE_TIME);
        }
        if (!is_a($this->from, 'Exposure\Model\User')) {
            throw new CommentException(self::EXCEPTION_INVALID_FROM);
        }
        if (!is_null($this->rating)) {
            $this->validateRating($this->rating);
        }
        $this->validateContent($this->content);
    }
}


