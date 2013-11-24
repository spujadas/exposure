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

class DefaultProjectOwnerRights {
    protected $id = null;
    
    protected $label;
    const LABEL_MAX_LENGTH = 32;

    /** @var ProjectRights */
    protected $projectRights = null;
    const EXCEPTION_INVALID_PROJECT_RIGHTS = 'invalid project rights';
    
    /** @var ViewRights */
    protected $viewRights = null;
    const EXCEPTION_INVALID_VIEW_RIGHTS = 'invalid view rights';
    
    public function getId() {
        return $this->id;
    }
    
    public function setLabel($label) {
        try {
            self::validateLabel($label);
        } catch (Exception $e) {
            $this->label = null;
            throw $e;
        }

        $this->label = $label;
        return $this->label;
    }

    public static function validateLabel($label) {
        StringValidator::validate($label, 
                array(
                    'not_empty' => true,
                    'max_length' => self::LABEL_MAX_LENGTH));
    }

    public function getLabel() {
        return $this->label;
    }
    
    public function getProjectRights() {
        return $this->projectRights;
    }
    
    public function setProjectRights(ProjectRights $projectRights) {
        $this->projectRights = $projectRights;
    }
    
    public function setViewRights(ViewRights $viewRights) {
        $this->viewRights = $viewRights;
    }
    
    public function getViewRights() {
        return $this->viewRights;
    }
    
    public function validate() {
        $this->validateLabel($this->label);
        if (!is_a($this->projectRights, 'Exposure\Model\ProjectRights')) {
                throw new DefaultProjectOwnerRightsException(self::EXCEPTION_INVALID_PROJECT_RIGHTS);
        }
        if (!is_a($this->viewRights, 'Exposure\Model\ViewRights')) {
                throw new DefaultProjectOwnerRightsException(self::EXCEPTION_INVALID_VIEW_RIGHTS);
        }
    }
}


