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

class CommentOnProjectOwner extends Comment {
    const EXCEPTION_OBJECT_USER_NOT_A_PROJECT_OWNER = 'object user not a project owner';
    const EXCEPTION_INVALID_PROJECT_OWNER = 'invalid project owner';
    
    /** @var User */
    protected $projectOwner = null;

    public function __construct() {
        $this->setType(parent::TYPE_COMMENT_ON_PROJECT_OWNER);
    }
    
    public function getProjectOwner() {
        return $this->projectOwner;
    }

    public function setProjectOwner(User $projectOwner) {
        try {
            $this->validateProjectOwner($projectOwner);
        } catch (Exception $e) {
            $this->projectOwner = null;
            throw $e;
        }
        $this->projectOwner = $projectOwner;
        return $this->projectOwner;
    }
    
    protected function validateProjectOwner(User $projectOwner) {
        if ($projectOwner->getType() != User::TYPE_PROJECT_OWNER) {
            throw new CommentOnProjectOwnerException(self::EXCEPTION_OBJECT_USER_NOT_A_PROJECT_OWNER);
        }
    }
    
    public function validate() {
        parent::validate();
        if ($this->type != parent::TYPE_COMMENT_ON_PROJECT_OWNER) {
            throw new CommentException(parent::EXCEPTION_TYPE_MISMATCH);
        }
        if (!is_a($this->projectOwner, 'Exposure\Model\User')) {
            throw new CommentOnProjectOwnerException(self::EXCEPTION_INVALID_PROJECT_OWNER);
        }
        $this->validateProjectOwner($this->projectOwner);
    }
}


