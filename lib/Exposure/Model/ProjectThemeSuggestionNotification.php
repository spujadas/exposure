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

class ProjectThemeSuggestionNotification extends Notification {
    const EVENT_SUBMITTED_THEME = 'submitted theme';
    const EVENT_ACCEPTED_THEME = 'accepted theme';
    const EVENT_REFUSED_THEME = 'refused theme';
    
    /** @var User */
    protected $from = null;
    const EXCEPTION_INVALID_FROM = 'invalid from';

    /** @var Theme */
    protected $parentTheme = null;
    const EXCEPTION_INVALID_PARENT_THEME = 'invalid parent theme';
    
    protected $themeName = null;

    public function __construct() {
        $this->setType(parent::TYPE_PROJECT_THEME_SUGGESTION);
    }
    
    protected function validateEvent($event) {
        if (!in_array($event, array(
                self::EVENT_ACCEPTED_THEME,
                self::EVENT_REFUSED_THEME,
                self::EVENT_SUBMITTED_THEME))) {
            throw new NotificationException(parent::EXCEPTION_INVALID_EVENT);
        }
    }
    
    public function getFrom() {
        return $this->from;
    }

    public function setFrom(User $from) {
        $this->from = $from;
        return $this->from;
    }

    public function getParentTheme() {
        return $this->parentTheme;
    }

    public function setParentTheme(Theme $parentTheme = null) {
        $this->parentTheme = $parentTheme;
        return $this->parentTheme;
    }

    public function getThemeName() {
        return $this->themeName;
    }

    public function setThemeName($themeName) {
        try {
            $this->validateThemeName($themeName);
        } catch (Exception $e) {
            $this->themeName = null;
            throw $e;
        }
        $this->themeName = $themeName;
        return $this->themeName;
    }
    
    protected function validateThemeName($themeName) {
        StringValidator::validate($themeName, array(
            'not_empty' => true,
            'max_length' => Theme::NAME_MAX_LENGTH
        ));
    }
    
    public function validate() {
        parent::validate();
        if ($this->type != parent::TYPE_PROJECT_THEME_SUGGESTION) {
            throw new NotificationException(parent::EXCEPTION_TYPE_MISMATCH);
        }
        $this->validateEvent($this->event);
        if (!is_a($this->from, 'Exposure\Model\User')) {
            throw new ProjectThemeSuggestionNotificationException(self::EXCEPTION_INVALID_FROM);
        }
        if (!is_null($this->parentTheme) && !is_a($this->parentTheme, 'Exposure\Model\Theme')) {
            throw new ProjectThemeSuggestionNotificationException(self::EXCEPTION_INVALID_PARENT_THEME);
        }
        $this->validateThemeName($this->themeName);
    }

    public function isStatusEditableByUser(User $user) {
        return $user->getNotifications()->contains($this);
    }
}


