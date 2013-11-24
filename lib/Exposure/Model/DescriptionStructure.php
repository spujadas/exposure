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

use Sociable\Model\MultiLanguageString;

use Doctrine\Common\Collections\ArrayCollection;

class DescriptionStructure {
    protected $id = null;
    
    /** @var Theme */
    protected $theme = null;
    const EXCEPTION_INVALID_THEME = 'invalid theme';
    
    /** @var ArrayCollection of MultiLanguageString */
    protected $sectionTitles;
    const SECTION_TITLE_MAX_LENGTH = 64;

    public function __construct() {
        $this->sectionTitles = new ArrayCollection;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getTheme() {
        return $this->theme;
    }

    public function setTheme(Theme $theme = null) {
        $this->theme = $theme;
        return $this->theme;
    }

    public function getSectionTitles() {
        return $this->sectionTitles;
    }

    public function addSectionTitle(MultiLanguageString $sectionTitle) {
        $this->validateSectionTitle($sectionTitle);
        $this->sectionTitles[] = $sectionTitle;
    }
    
    public function removeSectionTitle(MultiLanguageString $sectionTitle) {
        return $this->sectionTitles->removeElement($sectionTitle);
    }
    
    protected function validateSectionTitle(MultiLanguageString $sectionTitle) {
        $sectionTitle->validate(array(
            'not_empty' => true,
            'max_length' => self::SECTION_TITLE_MAX_LENGTH
        ));
    }

    public function validate() {
        if (!is_null($this->theme) && !is_a($this->theme, 'Exposure\Model\Theme')) {
            throw new DescriptionStructureException(self::EXCEPTION_INVALID_THEME);
        }
        foreach ($this->sectionTitles as $sectionTitle) {
            $this->validateSectionTitle($sectionTitle);
        }
    }
}


