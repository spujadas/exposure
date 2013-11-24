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

class CommentOnProject extends Comment {
    /** @var Project */
    protected $project = null;
    const EXCEPTION_INVALID_PROJECT = 'invalid project';
    
    public function __construct() {
        $this->setType(parent::TYPE_COMMENT_ON_PROJECT);
    }
    
    public function getProject() {
        return $this->project;
    }

    public function setProject(Project $project) {
        $this->project = $project;
        return $this->project;
    }
    
    public function validate() {
        parent::validate();
        if ($this->type != parent::TYPE_COMMENT_ON_PROJECT) {
            throw new CommentException(parent::EXCEPTION_TYPE_MISMATCH);
        }

        if (!is_a($this->project, 'Exposure\Model\Project')) {
            throw new CommentException(self::EXCEPTION_INVALID_PROJECT);
        }
    }
}


