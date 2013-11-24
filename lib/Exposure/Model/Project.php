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

use Sociable\Model\WebPresence,
    Sociable\Model\Location,
    Sociable\Utility\StringValidator,
    Sociable\Utility\SEO,
    Sociable\Model\Language,
    Sociable\Utility\NumberValidator;

use Doctrine\Common\Collections\ArrayCollection;

class Project {
    protected $id = null;

    protected $name = null;
    const NAME_MAX_LENGTH = 64;
    
    protected $urlSlug = null;
    const EXCEPTION_MISSING_URL_SLUG = 'missing URL slug';

    /** @var ModerationStatus */
    protected $moderationStatus = null;
    const EXCEPTION_INVALID_MODERATION_STATUS = 'invalid status';
    
    protected $languageCode = null; // app/country default
    
    /** @var \DateTime */
    protected $creationDateTime = null;
    const EXCEPTION_INVALID_CREATION_DATE_TIME = 'invalid creation date time';
    
    /** @var Theme */
    protected $theme = null;
    const EXCEPTION_INVALID_THEME = 'invalid theme';
    
    /** @var ArrayCollection of ProjectThemeSuggestionNotification,
     * ProjectWantNotification, ProjectModerationNotification, 
     * SponsorContributionNotification, SponsorReturnNotification,
     * CommentNotification */
    protected $notifications;
    const EXCEPTION_INVALID_NOTIFICATION_TYPE = 'invalid notification type';
    
    /** @var ArrayCollection of User */
    protected $owners; // inverse side
    
    /** @var ApprovableMultiLanguageString */
    protected $summary = null;
    const SUMMARY_MAX_LENGTH = 500;
    const EXCEPTION_INVALID_SUMMARY = 'invalid summary';
    
    /** @var ApprovableMultiLanguageString */
    protected $description = null;
    const DESCRIPTION_MAX_LENGTH = 50000;
    const EXCEPTION_INVALID_DESCRIPTION = 'invalid description';
    
    /** @var ApprovableMultiLanguageString */
    protected $audienceDescription = null;
    const AUDIENCE_DESCRIPTION_MAX_LENGTH = 500;
    const EXCEPTION_INVALID_AUDIENCE_DESCRIPTION = 'invalid audience description';
    
    protected $audienceRange = array('min' => 0, 'max' => 0);
    const EXCEPTION_INVALID_AUDIENCE_RANGE = 'invalid audience range';
    const AUDIENCE_RANGE_MAX = 1000000;

    /** @var ArrayCollection of ApprovableLabelledImage */
    protected $photos;
    const EXCEPTION_INVALID_PHOTO = 'invalid photo';
    const PHOTOS_MAX_COUNT = 50; // hard limit; soft limit determined by rights
    const PHOTO_MAX_SIZE = 2097152; // 2 Mo
    const EXCEPTION_TOO_MANY_PHOTOS = 'too many photos';
    const EXCEPTION_MISSING_PHOTO = 'missing photo';
    
    /** @var WebPresence */
    protected $webPresences;
    const EXCEPTION_INVALID_WEB_PRESENCE = 'invalid web presence';
    const NUMBER_WEB_PRESENCES_MAX = 10;
    const EXCEPTION_TOO_MANY_WEB_PRESENCES = 'too many web presences';

    protected $place = null;
    const EXCEPTION_INVALID_PLACE = 'invalid place';
    
    /** @var FinancialNeed */
    protected $financialNeed = null; // inverse side

    /** @var ArrayCollection of NonFinancialNeed */
    protected $nonFinancialNeeds; // inverse side

    /** @var \DateTime */
    protected $sponsoringDeadline = null;
    const EXCEPTION_INVALID_DEADLINE = 'invalid deadline';
    
    /** @var \DateTime */
    protected $eventDateTime = null;
    const EXCEPTION_INVALID_EVENT_DATE_TIME = 'invalid event date time';
    
    /** @var ArrayCollection of ProjectWant */
    protected $wants; // inverse side

    /** @var ArrayCollection of CommentOnProject */
    protected $comments; // inverse side
    
    /** @var int */
    protected $pageviews = 0;

    public function __construct() {
        $this->notifications = new ArrayCollection;
        $this->owners = new ArrayCollection;
        $this->photos = new ArrayCollection;
        $this->webPresences = new ArrayCollection;
        $this->nonFinancialNeeds = new ArrayCollection;
        $this->wants = new ArrayCollection;
        $this->comments = new ArrayCollection;
    }
    
    public function getId() {
        return $this->id;
    }

    public function setName($name) {
        try {
            self::validateName($name);
        }
        catch (Exception $e) {
            $this->name = null;
            throw $e;
        }
        $this->name = $name;
        $this->generateUrlSlug($name);
        return $this->name;
    }
    
    protected static function validateName($name) {
        StringValidator::validate($name, 
                array(
                    'not_empty' => true,
                    'max_length' => self::NAME_MAX_LENGTH)
                );
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function generateUrlSlug() {
        $this->urlSlug = SEO::generateSlug($this->name);
    }
    
    public function getUrlSlug() {
        return $this->urlSlug;
    }
    
    protected function validateUrlSlug($urlSlug) {
        StringValidator::validate($urlSlug, 
                array('max_length' => SEO::MAX_LENGTH, 'not_empty' => true));        
    }

    public function getModerationStatus() {
        return $this->moderationStatus;
    }

    public function setModerationStatus(ModerationStatus $moderationStatus) {
        try {
            $this->validateModerationStatus($moderationStatus);
        } catch (Exception $e) {
            $this->moderationStatus = null;
            throw $e;
        }
        $this->moderationStatus = $moderationStatus;
        return $this->moderationStatus;
    }

    protected function validateModerationStatus(ModerationStatus $moderationStatus) {
        $moderationStatus->validate();
    }

    public function setLanguageCode($languageCode = null) {
        if (!is_null($languageCode)) {
            try {
                $this->validateLanguageCode($languageCode);
            } catch (StringException $e) {
                $this->languageCode = null;
                throw $e;
            }
        }

        $this->languageCode = $languageCode;
        return $this->languageCode;
    }
    
    protected function validateLanguageCode($languageCode) {
        Language::validateCode($languageCode);
    }

    public function getLanguageCode() {
        return $this->languageCode;
    }
    
    public function setCreationDateTime(\DateTime $datetime) {
        $this->creationDateTime = $datetime;
        return $this->creationDateTime;
    }

    public function getCreationDateTime() {
        return $this->creationDateTime;
    }

    public function getTheme() {
        return $this->theme;
    }

    public function setTheme(Theme $theme) {
        $this->theme = $theme;
        return $this->theme;
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
        if (!in_array($notification->getType(), array(
            Notification::TYPE_PROJECT_THEME_SUGGESTION,
            Notification::TYPE_PROJECT_WANT,
            Notification::TYPE_PROJECT_MODERATION,
            Notification::TYPE_SPONSOR_CONTRIBUTION,
            Notification::TYPE_SPONSOR_RETURN,
            Notification::TYPE_COMMENT))) {
            throw new ProjectException(self::EXCEPTION_INVALID_NOTIFICATION_TYPE);
        }
    }
    public function getOwners() {
        return $this->owners;
    }

    public function setSummary(ApprovableMultiLanguageString $summary) {
        try {
            $this->validateSummary($summary);
        } catch (Exception $e) {
            $this->summary = null;
            throw $e;
        }
        $this->summary = $summary;
        return $this->summary;
        
    }
    
    protected function validateSummary(ApprovableMultiLanguageString $summary) {
        $summary->validate(array(
            'not_empty' => true,
            'max_length' => self::SUMMARY_MAX_LENGTH));
    }

    public function getSummary() {
        return $this->summary;
    }
    
    public function setDescription(ApprovableMultiLanguageString $description) {
        try {
            $this->validateDescription($description);
        } catch (Exception $e) {
            $this->description = null;
            throw $e;
        }
        $this->description = $description;
        return $this->description;
        
    }
    
    protected function validateDescription(ApprovableMultiLanguageString $description) {
        $description->validate(array(
            'max_length' => self::DESCRIPTION_MAX_LENGTH));
    }

    public function getDescription() {
        return $this->description;
    }

    public function setAudienceDescription(ApprovableMultiLanguageString $audienceDescription) {
        try {
            $this->validateAudienceDescription($audienceDescription);
        } catch (Exception $e) {
            $this->audienceDescription = null;
            throw $e;
        }
        $this->audienceDescription = $audienceDescription;
        return $this->audienceDescription;
        
    }
    
    protected function validateAudienceDescription(ApprovableMultiLanguageString $audienceDescription) {
        $audienceDescription->validate(array(
            'not_empty' => true,
            'max_length' => self::AUDIENCE_DESCRIPTION_MAX_LENGTH));
    }

    public function getAudienceDescription() {
        return $this->audienceDescription;
    }

    public function setAudienceRange ($min, $max) {
        try {
            $this->validateAudienceRange($min, $max);
        }
        catch (\Exception $e) {
            $this->audienceRange['min'] = 0;
            $this->audienceRange['max'] = 0;
            throw $e;
        }
        $this->audienceRange['min'] = $min;
        $this->audienceRange['max'] = $max;
        return $this->audienceRange;
    }

    public function getAudienceRange () {
        return $this->audienceRange;
    }

    protected function validateAudienceRange($min, $max) {
        NumberValidator::validate($min, array(
            'int' => true,
            'positive' => true,
            'max' => self::AUDIENCE_RANGE_MAX
        ));
        NumberValidator::validate($max, array(
            'int' => true,
            'positive' => true,
            'max' => self::AUDIENCE_RANGE_MAX
        ));
        if ($min > $max) {
            throw new ProjectException(self::EXCEPTION_INVALID_AUDIENCE_RANGE);
        }
    }

    public function addPhoto (ApprovableLabelledImage $photo) {
        if ($this->photos->count() == self::PHOTOS_MAX_COUNT) {
            throw new ProjectException(self::EXCEPTION_TOO_MANY_PHOTOS);
        }
        $this->photos[] = $photo;
    }

    public function getPhotos () {
        return $this->photos;
    }

    public function getPhotoById($id) {
        foreach ($this->photos as $photo) {
            if ($photo->getId() == $id) {
                return $photo;
            }
        }
        return null;
    }

    public function removePhoto(ApprovableLabelledImage $photo) {
        return $this->photos->removeElement($photo);
    }

    public function addWebPresence(WebPresence $webPresence) {
        if ($this->webPresences->count() == self::NUMBER_WEB_PRESENCES_MAX) {
            throw new ProjectException(self::EXCEPTION_TOO_MANY_WEB_PRESENCES);
        }
        $this->webPresences[] = $webPresence;
    }
    
    public function removeWebPresence(WebPresence $webPresence) {
        return $this->webPresences->removeElement($webPresence);
    }
    
    public function getWebPresences() {
        return $this->webPresences;
    }

    public function resetWebPresences() {
        $this->webPresences = new ArrayCollection;
    }

    public function getCountry() {
        if (is_null($this->place)) {
            return null;
        }
        if (is_a($this->place, 'Sociable\Model\Country')) {
            return $this->place;
        }
        return $this->place->getCountry();
    }

    public function getPlace() {
        return $this->place;
    }

    public function setPlace($place = null) {
        if (!is_null($place)) {
            $this->validatePlace($place);
        }
        $this->place = $place;
        return $this->place;
    }
    
    protected function validatePlace($place) {
        if (!is_a($place, 'Sociable\Model\Location') && !is_a($place, 'Sociable\Model\Country')) {
            throw new ProjectException(self::EXCEPTION_INVALID_PLACE);
        }
    }

    public function getFinancialNeed() {
        return $this->financialNeed;
    }

    public function getNonFinancialNeeds() {
        return $this->nonFinancialNeeds;
    }

    public function setSponsoringDeadline(\DateTime $datetime = null) {
        $this->sponsoringDeadline = $datetime;
        return $this->sponsoringDeadline;
    }

    public function getSponsoringDeadline() {
        return $this->sponsoringDeadline;
    }

    public function setEventDateTime(\DateTime $datetime = null) {
        $this->eventDateTime = $datetime;
        return $this->eventDateTime;
    }

    public function getEventDateTime() {
        return $this->eventDateTime;
    }

    public function getWants() {
        return $this->wants;
    }

    public function isWantedByOrganisation(SponsorOrganisation $organisation) {
        foreach ($this->wants as $want) {
            if ($want->getSponsorOrganisation() == $organisation) { 
                return true; 
            }
        }
        return false;
    }

    public function getComments() {
        return $this->comments;
    }

    public function getPageviews() {
        return $this->pageviews;
    }

    public function resetPageviews() {
        $this->pageviews = 0;
    }
    
    public function increasePageviews() {
        return ++$this->pageviews;
    }

    public function validate() {
        self::validateName($this->name);
        if (is_null($this->urlSlug)) {
            throw new ProjectException(self::EXCEPTION_MISSING_URL_SLUG);
        }
        $this->validateUrlSlug($this->urlSlug);
        if (!is_a($this->moderationStatus, 'Exposure\Model\ModerationStatus')) {
            throw new ProjectException(self::EXCEPTION_INVALID_MODERATION_STATUS);
        }
        $this->validateModerationStatus($this->moderationStatus);
        if (!is_null($this->languageCode)) {
            $this->validateLanguageCode($this->languageCode);
        }
        if (!is_a($this->creationDateTime, 'DateTime')) {
            throw new ProjectException(self::EXCEPTION_INVALID_CREATION_DATE_TIME);
        }
        if (!is_a($this->theme, 'Exposure\Model\Theme')) {
            throw new ProjectException(self::EXCEPTION_INVALID_THEME);
        }
        foreach ($this->notifications as $notification) {
            $this->validateNotification($notification);
        }
        if (!is_a($this->summary, 'Exposure\Model\ApprovableMultiLanguageString')) {
            throw new ProjectException(self::EXCEPTION_INVALID_SUMMARY);
        }
        $this->validateSummary($this->summary);
        if (!is_a($this->description, 'Exposure\Model\ApprovableMultiLanguageString')) {
            throw new ProjectException(self::EXCEPTION_INVALID_DESCRIPTION);
        }
        $this->validateDescription($this->description);
        if (!is_a($this->audienceDescription, 'Exposure\Model\ApprovableMultiLanguageString')) {
            throw new ProjectException(self::EXCEPTION_INVALID_AUDIENCE_DESCRIPTION);
        }
        $this->validateAudienceDescription($this->audienceDescription);
        $this->validateAudienceRange($this->audienceRange['min'], $this->audienceRange['max']);
        foreach ($this->photos as $photo) {
            if (!is_a($photo, 'Exposure\Model\ApprovableLabelledImage')) {
                throw new ProjectException(self::EXCEPTION_INVALID_PHOTO);
            }
        }
        if ($this->photos->count() == 0) {
            throw new ProjectException(self::EXCEPTION_MISSING_PHOTO);
        }
        foreach ($this->webPresences as $webPresence) {
            if (!is_a($webPresence, 'Sociable\Model\WebPresence')) {
                throw new ProjectException(self::EXCEPTION_INVALID_WEB_PRESENCE);
            }
        }
        if (!is_null($this->place)) {
            $this->validatePlace($this->place);
        }
        if (!is_null($this->sponsoringDeadline) && !is_a($this->sponsoringDeadline, 'DateTime')) {
            throw new ProjectException(self::EXCEPTION_INVALID_SPONSORING_DEADLINE);
        }
        if (!is_null($this->eventDateTime) && !is_a($this->eventDateTime, 'DateTime')) {
            throw new ProjectException(self::EXCEPTION_INVALID_EVENT_DATE_TIME);
        }

    }
    
    public function isEditable() {
        switch ($this->moderationStatus->getStatus()) {
        case ModerationStatus::STATUS_FLAGGED:
        case ModerationStatus::STATUS_REJECTED:
            return false;
        default:
            return true;
        }
    }

    public function isPublishable() {
        if (!$this->isEditable()) {
            return false;
        }
        switch ($this->moderationStatus->getStatus()) {
        case ModerationStatus::STATUS_SUBMITTED_FIRST_TIME:
        case ModerationStatus::STATUS_SUBMITTED:
        case ModerationStatus::STATUS_APPROVED:
            return false;
        default:
            return true;
        }
    }

    public function isContributedToByOrganisation(SponsorOrganisation $organisation) {
        foreach ($this->getContributions() as $contribution) {
            if ($contribution->getContributor() == $organisation) {
                return true;
            }
        }
        return false;
    }

    public function getContributions() {
        if (is_null($this->financialNeed)) {
            $contributions = new ArrayCollection;
        }
        else {
            $contributions = $this->financialNeed->getContributions();
        }
        foreach ($this->nonFinancialNeeds as $nonFinancialNeed) {
            if (!is_null($contribution = $nonFinancialNeed->getContribution())) {
                $contributions->add($contribution);
            }
        }
        return $contributions;
    }
}


