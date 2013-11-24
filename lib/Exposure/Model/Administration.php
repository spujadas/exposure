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

use Doctrine\Common\Collections\ArrayCollection;

class Administration {
    protected $id = null;
    
    /** @var ArrayCollection of ProjectThemeSuggestionNotification,
     *  ProjectModerationNotification, ProfileModerationNotification */
    protected $notifications;
    const EXCEPTION_INVALID_NOTIFICATION_TYPE = 'invalid notification type';
    
    protected $label;
    const LABEL_MAX_LENGTH = 32;
    const EXCEPTION_NOT_IN_CATALOGUE = 'not in catalogue';

    public function getId() {
        return $this->id;
    }
    
    public function __construct() {
        $this->notifications = new ArrayCollection;
    }
    
    public function setLabel($label) {
        try {
            $this->validateLabel($label);
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
    
    public function getNotifications() {
        return $this->notifications;
    }
    
    public function addNotification(Notification $notification) {
        $this->validateNotification($notification);
        $this->notifications[] = $notification;
    }
    
    public function removeNotification(Notification $notification) {
        return $this->notifications->removeElement($notification);
    }
    
    protected function validateNotification(Notification $notification) {
        $type = $notification->getType();
        if (($type != Notification::TYPE_PROJECT_THEME_SUGGESTION) 
                && ($type != Notification::TYPE_PROFILE_MODERATION)
                && ($type != Notification::TYPE_PROJECT_MODERATION)) {
            throw new AdministrationException(self::EXCEPTION_INVALID_NOTIFICATION_TYPE);
        }
    }
    
    public function validate() {
        $this->validateLabel($this->label);
        foreach ($this->notifications as $notification) {
            $this->validateNotification($notification);
        }
    }
}


