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
    Sociable\Utility\StringValidator,
    Sociable\Utility\NumberValidator;

use Doctrine\Common\Collections\ArrayCollection;

class Theme {
    const DEPTH_MAX = 1;
    const EXCEPTION_TOO_DEEP = 'too deep';
    
    protected $id;

    protected $label;
    const LABEL_MAX_LENGTH = 32;

    protected $path = null; 
        // http://docs.mongodb.org/manual/tutorial/model-tree-structures-with-materialized-paths/
    
    /** @var MultiLanguageString */
    protected $name;
    const NAME_MAX_LENGTH = 64;

    /** @var Theme */
    protected $parentTheme = null;
    
    /** @var DescriptionStructure */
    protected $descriptionStructure = null; // inverse side
    
    /** @var ArrayCollection of Theme */
    protected $childrenThemes; // inverse side
    const EXCEPTION_HAS_CHILDREN = 'has children';
    
    /** @var ArrayCollection of SponsorReturnOnFinancialContribution */
    protected $sponsorReturnsOnFinancialContribution; // inverse side

    public function __construct() {
        $this->childrenThemes = new ArrayCollection;
    }

    public function getId() {
        return $this->id;
    }
    
    public function getDescriptionStructure() {
        return $this->descriptionStructure;
    }
    
    public function setLabel($label) {
        try {
            self::validateLabel($label);
        } catch (Exception $e) {
            $this->label = null;
            throw $e;
        }

        $this->label = $label;
        $this->updatePath();
        return $this->label;
    }

    public static function validateLabel($label) {
        StringValidator::validate($label, array(
            'max_length' => self::LABEL_MAX_LENGTH,
            'not_empty' => true)
        );
    }

    public function getLabel() {
        return $this->label;
    }
    
    public function setName(MultiLanguageString $name) {
        try {
            $this->validateName($name);
        } catch (Exception $e) {
            $this->name = null;
            throw $e;
        }
        $this->name = $name;
        return $this->name;
    }
    
    protected function validateName(MultiLanguageString $name) {
        $name->validate(array(
            'not_empty' => true,
            'max_length' => self::NAME_MAX_LENGTH));
    }

    public function getName() {
        return $this->name;
    }
    
    public function setParentTheme(Theme $parentTheme = null) {
        if (!is_null($parentTheme)) {
            try {
                $parentTheme->validateDepth(self::DEPTH_MAX - 1);
            } catch (Exception $e) {
                $this->parentTheme = null;
                throw $e;
            }
        }

        $this->parentTheme = $parentTheme;
        $this->updatePath();
        return $this->parentTheme;
    }

    public function getParentTheme() {
        return $this->parentTheme;
    }

    protected function updatePath() {
        // update this's path
        if (is_null($this->parentTheme)) {
            $this->path = '|' . $this->getLabel() . '|';
        }
        else {
            $this->path = null;
            $currentTheme = $this;
            while (!is_null($currentTheme)) {
                $this->path = $currentTheme->getLabel() . '|' . $this->path;
                $currentTheme = $currentTheme->getParentTheme();
            }
            $this->path = '|' . $this->path;
        }


        // update childrens' path
        foreach ($this->childrenThemes as $childTheme) {
            $childTheme->updatePath();
        }
    }

    public function getPath() {
        return $this->path;
    }

    public function getChildrenThemes() {
        return $this->childrenThemes;
    }

    public function getSponsorReturnsOnFinancialContribution() {
        return $this->sponsorReturnsOnFinancialContribution;
    }

    public function validate() {
        $this->validateLabel($this->label);
        $this->validateName($this->name);
        $this->validateDepth(self::DEPTH_MAX);
    }
    
    public function validateDepth($depth) {
        NumberValidator::validate($depth, 
                array('int' => true, 'positive' => true));
        if (is_null($this->parentTheme)) {
            return;
        }
        if ($depth == 0) {
            throw new ThemeException(self::EXCEPTION_TOO_DEEP);
        }
        $this->parentTheme->validateDepth($depth - 1);
    }

    public function getTopLevelTheme() {
        if (is_null($this->parentTheme)) {
            return $this;
        }
        return $this->parentTheme;
    }
        
    public function checkForOrphansBeforeRemove() {
        if ($this->getChildrenThemes()->count() > 0) {
            throw new ThemeException(self::EXCEPTION_HAS_CHILDREN);
        }
    }
}


