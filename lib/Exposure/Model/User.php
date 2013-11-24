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

use Sociable\Model\ConfirmationCode,
    Sociable\Model\LabelledImage,
    Sociable\Model\Address,
    Sociable\Model\Language,
    Sociable\Model\Currency,
    Sociable\Model\MultiLanguageString,
    Exposure\Model\Subscription;

use Doctrine\Common\Collections\ArrayCollection;

class User extends \Sociable\Model\User {
    /** @var ConfirmationCode */
    protected $emailConfirmationCode = null;
    const EXCEPTION_INVALID_EMAIL_CONFIRMATION_CODE = 'invalid email confirmation code';
    
    /** @var ConfirmationCode */
    protected $passwordResetCode = null;
    const EXCEPTION_INVALID_PASSWORD_RESET_CODE = 'invalid password reset code';
    
    protected $status = null;
    const STATUS_REGISTERED = 'registered';
    const STATUS_VALIDATED = 'validated';
    const STATUS_DISABLED = 'disabled';
    const EXCEPTION_INVALID_STATUS = 'invalid status';
    
    /** @var ModerationStatus */
    protected $moderationStatus = null;
    const EXCEPTION_INVALID_MODERATION_STATUS = 'invalid status';

    /** @var \DateTime */
    protected $registrationDateTime = null;
    const EXCEPTION_INVALID_REGISTRATION_DATE_TIME = 'invalid registration date time';
    
    protected $firstTime = null; // can be null
    const FIRST_TIME_PROFILE = 'profile';
    const FIRST_TIME_ORGANISATION = 'organisation';
    const FIRST_TIME_PROJECT = 'project';
    const EXCEPTION_INVALID_FIRST_TIME = 'invalid first time';
    
    protected $type = null;
    const TYPE_PROJECT_OWNER = 'project owner';
    const TYPE_SPONSOR = 'sponsor';
    // const TYPE_ANY = 'any'; // V2
    const EXCEPTION_INVALID_TYPE = 'invalid type';

    /** @var ArrayCollection of Project */
    protected $ownedProjects; 
    const EXCEPTION_INVALID_OWNED_PROJECT = 'invalid owned project';
    const EXCEPTION_DUPLICATE_OWNED_PROJECT = 'duplicate owned project';
    const EXCEPTION_TOO_MANY_OWNED_PROJECTS = 'too many owned projects';
    const OWNED_PROJECTS_MAX_NUMBER = 100;

    /** @var ArrayCollection of SponsorOrganisation */
    protected $visibleSponsorOrganisations;
    const EXCEPTION_INVALID_SPONSOR_ORGANISATION = 'invalid sponsor organisation';

    /** @var ArrayCollection of LabelledImage */
    protected $tempDraftProjectPhotos;
    const EXCEPTION_INVALID_TEMP_DRAFT_PROJECT_PHOTO = 'invalid temp draft photo';
    const EXCEPTION_DUPLICATE_TEMP_DRAFT_PROJECT_PHOTO = 'duplicate temp draft photo';
    
    /** @var ArrayCollection of ProfileModerationNotification, ProjectThemeSuggestionNotification */
    protected $notifications ;
    const EXCEPTION_INVALID_NOTIFICATION_TYPE = 'invalid notification type';
    
    /** @var ArrayCollection of SponsorOrganisation */
    protected $sponsorOrganisations; // inverse side
    const SPONSOR_ORGANISATIONS_MAX_NUMBER = 1; // V1
    const EXCEPTION_TOO_MANY_SPONSOR_ORGANISATIONS = 'too many sponsor organisations';
    
    /** @var LabelledImage */
    protected $photo = null;
    const EXCEPTION_INVALID_PHOTO = 'invalid photo';
    const PHOTO_MAX_SIZE = 2097152; // 2 Mo

    protected $place = null;
    const EXCEPTION_INVALID_PLACE = 'invalid place';
    
    /** @var Address */
    protected $billingAddress = null;
    const EXCEPTION_INVALID_BILLING_ADDRESS = 'invalid billing address';
    
    /** @var MultiLanguageString */
    protected $presentation = null;
    const PRESENTATION_MAX_LENGTH = 140;
    const EXCEPTION_INVALID_PRESENTATION = 'invalid presentation';
        
    /** @var Subscription */
    protected $nextSubscription = null;
    const EXCEPTION_INVALID_NEXT_SUBSCRIPTION = 'invalid next subscription';
    
    /** @var Subscription */
    protected $currentSubscription = null;
    const EXCEPTION_INVALID_CURRENT_SUBSCRIPTION = 'invalid current subscription';
    
    /** @var ArrayCollection of Subscription */
    protected $pastSubscriptions;
    const EXCEPTION_INVALID_PAST_SUBSCRIPTION = 'invalid past subscription';
    
    protected $languageCode = null; // app/country default
    protected $currencyCode = null; // app/country default
    
    /** @var ArrayCollection of Project */
    protected $bookmarkedProjects;
    const EXCEPTION_INVALID_BOOKMARKED_PROJECT = 'invalid bookmarked project';
    const EXCEPTION_DUPLICATE_BOOKMARKED_PROJECT = 'duplicate bookmarked project';
    
    /** @var ProjectOwnerPreferences */
    protected $projectOwnerPreferences = null;
    const EXCEPTION_INVALID_PROJECT_OWNER_PREFERENCES = 'invalid project owner preferences';
    
    /** @var SponsorUserPreferences */
    protected $sponsorUserPreferences = null;
    const EXCEPTION_INVALID_SPONSOR_USER_PREFERENCES = 'invalid sponsor user preferences';

    /** @var ArrayCollection of CommentOnProjectOwner */
    protected $commentsOnProjectOwner = null; // inverse side
    
    public function __construct() {
        $this->ownedProjects = new ArrayCollection;
        $this->notifications = new ArrayCollection;
        $this->sponsorOrganisations = new ArrayCollection;
        $this->visibleSponsorOrganisations = new ArrayCollection;
        $this->pastSubscriptions = new ArrayCollection;
        $this->bookmarkedProjects = new ArrayCollection;
        $this->commentsOnProjectOwner = new ArrayCollection;
        $this->tempDraftProjectPhotos = new ArrayCollection;
    }

    public function getEmailConfirmationCode() {
        return $this->emailConfirmationCode;
    }

    public function setEmailConfirmationCode(ConfirmationCode $emailConfirmationCode = null) {
        $this->emailConfirmationCode = $emailConfirmationCode;
        return $this->emailConfirmationCode;
    }

    public function getPasswordResetCode() {
        return $this->passwordResetCode;
    }

    public function setPasswordResetCode(ConfirmationCode $passwordResetCode = null) {
        $this->passwordResetCode = $passwordResetCode;
        return $this->passwordResetCode;
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
        if (($status != self::STATUS_DISABLED) 
                && ($status != self::STATUS_REGISTERED) 
                && ($status != self::STATUS_VALIDATED)) {
            throw new UserException(self::EXCEPTION_INVALID_STATUS);
        }
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
    
    
    public function getRegistrationDateTime() {
        return $this->registrationDateTime;
    }
            
    public function setRegistrationDateTime(\DateTime $datetime) {
        $this->registrationDateTime = $datetime;
        return $this->registrationDateTime;
    }
    
    public function getFirstTime() {
        return $this->firstTime;
    }

    public function setFirstTime($firstTime = null) {
        if (!is_null($firstTime)) {
            try {
                $this->validateFirstTime($firstTime);
            } catch (Exception $e) {
                $this->firstTime = null;
                throw $e;
            }
        }
        $this->firstTime = $firstTime;
        return $this->firstTime;
    }
    
    protected function validateFirstTime($firstTime) {
        if (($firstTime != self::FIRST_TIME_ORGANISATION) 
                && ($firstTime != self::FIRST_TIME_PROFILE) 
                && ($firstTime != self::FIRST_TIME_PROJECT)) {
            throw new UserException(self::EXCEPTION_INVALID_FIRST_TIME);
        }
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
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
        if (($type != self::TYPE_PROJECT_OWNER) 
                && ($type != self::TYPE_SPONSOR)) {
            throw new UserException(self::EXCEPTION_INVALID_TYPE);
        }
    }

    public function getOwnedProjects() {
        return $this->ownedProjects;
    }

    public function addOwnedProject(Project $ownedProject) {
        if ($this->ownedProjects->count() == self::OWNED_PROJECTS_MAX_NUMBER) {
            throw new UserException(self::EXCEPTION_TOO_MANY_OWNED_PROJECTS);
        }
        if ($this->ownsProject($ownedProject)) {
            throw new UserException(self::EXCEPTION_DUPLICATE_OWNED_PROJECT);
        }
        $this->ownedProjects[] = $ownedProject;
    }
    
    public function existsOwnedProjectByName($name) {
        foreach ($this->ownedProjects as $ownedProject) {
            if ($ownedProject->getName() == $name) {
                return true;
            }
        }
        return false;
    }
    
    public function ownsProject(Project $project) {
        return $this->ownedProjects->contains($project);
    }
    
    public function removeOwnedProject(Project $ownedProject) {
        return $this->ownedProjects->removeElement($ownedProject);
    }

    public function belongsToOrganisation(SponsorOrganisation $organisation) {
        return $this->sponsorOrganisations->contains($organisation);
    }

    public function getVisibleSponsorOrganisations() {
        return $this->visibleSponsorOrganisations;
    }

    public function addVisibleSponsorOrganisation(SponsorOrganisation $organisation) {
        $this->visibleSponsorOrganisations[] = $organisation;
    }

    public function canSeeSponsorOrganisation(SponsorOrganisation $organisation) {
        $canSeeSponsor = false;

        // if subscription allows seeing wanting sponsors...
        if (!is_null($this->getCurrentSubscription())) {
            $canSeeSponsor = $this->getCurrentSubscription()
                ->getTypeAndDuration()->getType()->getViewRights()->getCanSeeSponsors();
            // ... and sponsor and user have at least one wanted project in common: good to go
            foreach ($this->ownedProjects as $project) {
                if ($project->isWantedByOrganisation($organisation)) {
                    return true;
                }
            }
        }

        // alternative is that sponsor was previously visible
        return $this->getVisibleSponsorOrganisations()->contains($organisation);
    }

    public function wantsProject(Project $project) {
        foreach ($this->sponsorOrganisations as $organisation) {
            if ($organisation->wantsProject($project)) {
                return true;
            }
        }
        return false;
    }

    public function getTempDraftProjectPhotos() {
        return $this->tempDraftProjectPhotos;
    }

    public function getTempDraftProjectPhotoById($id) {
        foreach ($this->tempDraftProjectPhotos as $tempDraftProjectPhoto) {
            if ($tempDraftProjectPhoto->getId() == $id) {
                return $tempDraftProjectPhoto;
            }
        }
        return null;
    }

    public function addTempDraftProjectPhoto(LabelledImage $tempDraftProjectPhoto) {
        if ($this->existsTempDraftProjectPhotoById($tempDraftProjectPhoto->getId())) {
            throw new UserException(self::EXCEPTION_DUPLICATE_TEMP_DRAFT_PROJECT_PHOTO);
        }
        $this->tempDraftProjectPhotos[] = $tempDraftProjectPhoto;
    }
    
    protected function existsTempDraftProjectPhotoById($id) {
        foreach ($this->tempDraftProjectPhotos as $tempDraftProjectPhoto) {
            if ($tempDraftProjectPhoto->getId() == $id) {
                return true;
            }
        }
        return false;
    }

    public function removeTempDraftProjectPhotoById($id) {
        foreach ($this->tempDraftProjectPhotos as $tempDraftProjectPhoto) {
            if ($tempDraftProjectPhoto->getId() == $id) {
                return $this->removeTempDraftProjectPhoto($tempDraftProjectPhoto);
            }
        }
        return false;
    }
    
    public function removeTempDraftProjectPhoto(LabelledImage $tempDraftProjectPhoto) {
        return $this->tempDraftProjectPhotos->removeElement($tempDraftProjectPhoto);
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
        if (($type != Notification::TYPE_PROFILE_MODERATION) 
                && ($type != Notification::TYPE_PROJECT_THEME_SUGGESTION)) {
            throw new UserException(self::EXCEPTION_INVALID_NOTIFICATION_TYPE);
        }
    }
    
    public function getSponsorOrganisations() {
        return $this->sponsorOrganisations;
    }

    public function hasWantedProjects() {
        foreach ($this->sponsorOrganisations as $sponsorOrganisation) {
            if ($sponsorOrganisation->getWants()->count()) return true;
        }
        return false;
    }

    public function hasSponsoredProjects() {
        foreach ($this->sponsorOrganisations as $sponsorOrganisation) {
            if ($sponsorOrganisation->getSponsoredProjects()->count()) return true;
        }
        return false;
    }

    public function setPhoto (LabelledImage $photo) {
        try {
            $this->validatePhoto($photo);
        }
        catch (Exception $e) {
            $this->photo = null;
            throw $e;
        }
        $this->photo = $photo;
        return $this->photo;
    }
    
    public function getPhoto () {
        return $this->photo;
    }

    public function validatePhoto (LabelledImage $photo) {
        $photo->validate();
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

    public function setPlace($place) {
        $this->validatePlace($place);
        $this->place = $place;
        return $this->place;
    }
    
    protected function validatePlace($place) {
        if (!is_a($place, 'Sociable\Model\Location') && !is_a($place, 'Sociable\Model\Country')) {
            throw new UserException(self::EXCEPTION_INVALID_PLACE);
        }
    }

    public function getBillingAddress() {
        return $this->billingAddress;
    }

    public function setBillingAddress(Address $billingAddress = null) {
        $this->billingAddress = $billingAddress;
        return $this->billingAddress;
    }
    
    public function setPresentation(MultiLanguageString $presentation) {
        try {
            $this->validatePresentation($presentation);
        } catch (Exception $e) {
            $this->presentation = null;
            throw $e;
        }
        $this->presentation = $presentation;
        return $this->presentation;
        
    }
    
    protected function validatePresentation(MultiLanguageString $presentation) {
        $presentation->validate(array(
            'not_empty' => true,
            'max_length' => self::PRESENTATION_MAX_LENGTH));
    }

    public function getPresentation() {
        return $this->presentation;
    }

    public function getNextSubscription() {
        return $this->nextSubscription;
    }

    public function setNextSubscription(Subscription $subscription = null) {
        $this->nextSubscription = $subscription;
        return $this->nextSubscription;
    }

    public function getCurrentSubscription() {
        return $this->currentSubscription;
    }

    public function setCurrentSubscription(Subscription $subscription = null) {
        $this->currentSubscription = $subscription;
        return $this->currentSubscription;
    }

    public function getPastSubscriptions() {
        return $this->pastSubscriptions;
    }

    public function addPastSubscription(Subscription $subscription) {
        $this->pastSubscriptions[] = $subscription;
    }
    
    public function removePastSubscription(Subscription $subscription) {
        return $this->pastSubscriptions->removeElement($subscription);
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

    public function setCurrencyCode($currencyCode = null) {
        if (!is_null($currencyCode)) {
            try {
                $this->validateCurrencyCode($currencyCode);
            } catch (StringException $e) {
                $this->currencyCode = null;
                throw $e;
            }
        }
        $this->currencyCode = $currencyCode;
        return $this->currencyCode;
    }
    
    protected function validateCurrencyCode($currencyCode) {
        Currency::validateCode($currencyCode);
    }

    public function getCurrencyCode() {
        return $this->currencyCode;
    }

    public function getBookmarkedProjects() {
        return $this->bookmarkedProjects;
    }

    public function addBookmarkedProject(Project $bookmarkedProject) {
        if ($this->existsBookmarkedProjectByName($bookmarkedProject->getName())) {
            throw new UserException(self::EXCEPTION_DUPLICATE_BOOKMARKED_PROJECT);
        }
        $this->bookmarkedProjects[] = $bookmarkedProject;
    }

    protected function existsBookmarkedProjectByName($name) {
        foreach ($this->bookmarkedProjects as $bookmarkedProject) {
            if ($bookmarkedProject->getName() == $name) {
                return true;
            }
        }
        return false;
    }
    
    public function hasBookmarkedProject(Project $project) {
        return $this->bookmarkedProjects->contains($project);
    }
    
    public function removeBookmarkedProject(Project $bookmarkedProject) {
        return $this->bookmarkedProjects->removeElement($bookmarkedProject);
    }

    public function getProjectOwnerPreferences() {
        return $this->projectOwnerPreferences;
    }

    public function setProjectOwnerPreferences(ProjectOwnerPreferences $projectOwnerPreferences = null) {
        $this->projectOwnerPreferences = $projectOwnerPreferences;
        return $this->projectOwnerPreferences;
    }

    public function getSponsorUserPreferences() {
        return $this->sponsorUserPreferences;
    }

    public function setSponsorUserPreferences(SponsorUserPreferences $sponsorUserPreferences = null) {
        $this->sponsorUserPreferences = $sponsorUserPreferences;
        return $this->sponsorUserPreferences;
    }

    public function getReceiveNotificationsByEmail() {
        return
            (($this->getType() == User::TYPE_PROJECT_OWNER) && $this->getProjectOwnerPreferences()->getReceiveNotificationsByEmail())
            ||
            (($this->getType() == User::TYPE_SPONSOR) && $this->getSponsorUserPreferences()->getReceiveNotificationsByEmail());
    }

    public function getCommentsOnProjectOwner() {
        return $this->commentsOnProjectOwner;
    }

    public function validatePartial() {
        parent::validate();
        if (!is_null($this->emailConfirmationCode) 
                && !is_a($this->emailConfirmationCode, 'Sociable\Model\ConfirmationCode')) {
            throw new UserException(self::EXCEPTION_INVALID_EMAIL_CONFIRMATION_CODE);
        }
        if (!is_null($this->passwordResetCode) 
                && !is_a($this->passwordResetCode, 'Sociable\Model\ConfirmationCode')) {
            throw new UserException(self::EXCEPTION_INVALID_PASSWORD_RESET_CODE);
        }

        $this->validateStatus($this->status);
        if (!is_a($this->moderationStatus, 'Exposure\Model\ModerationStatus')) {
            throw new UserException(self::EXCEPTION_INVALID_STATUS);
        }
        $this->validateModerationStatus($this->moderationStatus);
        if (!is_a($this->registrationDateTime, 'DateTime')) {
            throw new UserException(self::EXCEPTION_INVALID_REGISTRATION_DATE_TIME);
        }
        if (!is_null($this->firstTime)) {
            $this->validateFirstTime($this->firstTime);
        }
        $this->validateType($this->type);
        foreach ($this->ownedProjects as $ownedProject) {
            if (!is_a($ownedProject, 'Exposure\Model\Project')) {
                throw new UserException(self::EXCEPTION_INVALID_OWNED_PROJECT);
            }
        }
        foreach ($this->visibleSponsorOrganisations as $sponsorOrganisation) {
            if (!is_a($sponsorOrganisation, 'Exposure\Model\SponsorOrganisation')) {
                throw new UserException(self::EXCEPTION_INVALID_SPONSOR_ORGANISATION);
            }
        }
        foreach ($this->tempDraftProjectPhotos as $tempDraftProjectPhoto) {
            if (!is_a($tempDraftProjectPhoto, 'Sociable\Model\LabelledImage')) {
                throw new UserException(self::EXCEPTION_INVALID_TEMP_DRAFT_PROJECT_PHOTO);
            }
        }
        if (!is_null($this->billingAddress) 
                && !is_a($this->billingAddress, 'Sociable\Model\Address')) {
            throw new UserException(self::EXCEPTION_INVALID_BILLING_ADDRESS);
        }
        if (!is_null($this->nextSubscription) 
                && !is_a($this->nextSubscription, 'Exposure\Model\Subscription')) {
            throw new UserException(self::EXCEPTION_INVALID_NEXT_SUBSCRIPTION);
        }
        if (!is_null($this->currentSubscription) 
                && !is_a($this->currentSubscription, 'Exposure\Model\Subscription')) {
            throw new UserException(self::EXCEPTION_INVALID_CURRENT_SUBSCRIPTION);
        }
        foreach ($this->pastSubscriptions as $pastSubscription) {
            if (!is_a($pastSubscription, 'Exposure\Model\Subscription')) {
                throw new UserException(self::EXCEPTION_INVALID_PAST_SUBSCRIPTION);
            }
        }
        if (!is_null($this->languageCode)) {
            $this->validateLanguageCode($this->languageCode);
        }
        if (!is_null($this->currencyCode)) {
            $this->validateCurrencyCode($this->currencyCode);
        }
        foreach ($this->bookmarkedProjects as $bookmarkedProject) {
            if (!is_a($bookmarkedProject, 'Exposure\Model\Project')) {
                throw new UserException(self::EXCEPTION_INVALID_BOOKMARKED_PROJECT);
            }
        }
        if (!is_null($this->projectOwnerPreferences)
                && !is_a($this->projectOwnerPreferences, 'Exposure\Model\ProjectOwnerPreferences')) {
            throw new UserException(self::EXCEPTION_INVALID_PROJECT_OWNER_PREFERENCES);
        }
        if (!is_null($this->sponsorUserPreferences)
                && !is_a($this->sponsorUserPreferences, 'Exposure\Model\SponsorUserPreferences')) {
            throw new UserException(self::EXCEPTION_INVALID_SPONSOR_USER_PREFERENCES);
        }
    }

    public function validate() {
        $this->validatePartial();
        $this->validateName($this->name);
        $this->validateSurname($this->surname);
        if (!is_a($this->photo, 'Sociable\Model\LabelledImage')) {
            throw new UserException(self::EXCEPTION_INVALID_PHOTO);
        }
        $this->validatePhoto($this->photo);
        $this->validatePlace($this->place);
        if (!is_a($this->presentation, 'Sociable\Model\MultiLanguageString')) {
            throw new UserException(self::EXCEPTION_INVALID_PRESENTATION);
        }
        $this->validatePresentation($this->presentation);
    }
    
    public function getNotificationCount($byStatus = array()) {
        // total number of notifications if no argument passed
        if (empty($byStatus)) {
            $count = $this->notifications->count();
            foreach ($this->ownedProjects as $project) {
                $count += $project->getNotifications()->count();
            }
            return $count;
        }

        // number of notifications matching a status in argument array
        $count = 0;
        foreach ($this->notifications as $notification) {
            if (in_array($notification->getStatus(), $byStatus)) { $count ++; }
        }
        foreach ($this->ownedProjects as $project) {
            foreach ($project->getNotifications() as $notification) {
                if (in_array($notification->getStatus(), $byStatus)) { $count ++; }
            }
        }
        foreach ($this->sponsorOrganisations as $organisation) {
            foreach ($organisation->getNotifications() as $notification) {
                if (in_array($notification->getStatus(), $byStatus)) { $count ++; }
            }
        }
        return $count;
    }

    public function canSubscribe() {
        if ($this->getType() != self::TYPE_PROJECT_OWNER) {
            return false;
        }
        if (is_null($this->getCurrentSubscription()) 
            || is_null($this->getNextSubscription())
            || ($this->getCurrentSubscription()->getStatus() != Subscription::STATUS_ACTIVE)
            || ($this->getNextSubscription()->getStatus() != Subscription::STATUS_ACTIVE)
            ) {
            return true;
        }
        return false;
    }

    public function addSubscription(Subscription $subscription) {
        if ($this->getType() != self::TYPE_PROJECT_OWNER) {
            return false;
        }

        // set current subscription if available
        if (is_null($this->currentSubscription)) {
            $this->setCurrentSubscription($subscription);
            return true;
        }

        switch ($this->currentSubscription->getStatus()) {
        case Subscription::STATUS_PENDING_PAYMENT: // requires "manual" clean up
            return false;
        case Subscription::STATUS_ACTIVE: // current is active and next is free
            if (is_null($this->nextSubscription)) {
                $this->setNextSubscription($subscription);
                return true;
            }
            break;
        case Subscription::STATUS_INACTIVE:
            break;
        }

        // at this stage, current subscription is inactive, so move everything down a notch
        return $this->cascadeSubscriptionsOnCurrentSubscriptionInactive($subscription);
    }

    protected function cascadeSubscriptionsOnCurrentSubscriptionInactive(Subscription $subscription = null) {
        if ($this->currentSubscription->getStatus() != Subscription::STATUS_INACTIVE) {
            return false;
        }

        // move current to past
        $this->addPastSubscription($this->currentSubscription);

        // no next subscription => subscription becomes current
        if (is_null($this->nextSubscription)) {
            $this->setCurrentSubscription($subscription);
            return true;
        }

        // next subscription becomes current if active and $subscription is next
        switch ($this->nextSubscription->getStatus()) {
        case Subscription::STATUS_INACTIVE: // shouldn't happen
            return false;
        case Subscription::STATUS_PENDING_PAYMENT: // no valid next subscription
            $this->setCurrentSubscription($subscription);
            return true;
        case Subscription::STATUS_ACTIVE: // next becomes active
            $this->setCurrentSubscription($this->nextSubscription);
            $this->currentSubscription->startSubscription();
            $this->setNextSubscription($subscription);
            return true;
        }

        return false; // junk
    }

    public function expireCurrentSubscription($forceExpiration = false) {
        if (is_null($this->currentSubscription)) {
            return false;
        }
        if ($this->getCurrentSubscription()->getStatus() != Subscription::STATUS_ACTIVE) {
            return false;
        }
        if (($this->getCurrentSubscription()->getEndDateTime() > new \DateTime) 
            && !$forceExpiration) {
            return false;
        }
        $this->currentSubscription->setStatus(Subscription::STATUS_INACTIVE);
        return $this->cascadeSubscriptionsOnCurrentSubscriptionInactive(null);
    }

    public function removeSubscription(Subscription $subscription) {
        if ($this->nextSubscription == $subscription) {
            $this->setNextSubscription(null);
            return true;
        }
        if ($this->currentSubscription == $subscription) {
            // cascade next subscription if active
            if (!is_null($this->nextSubscription)
                && ($this->nextSubscription->getStatus() == Subscription::STATUS_ACTIVE)) {
                $this->setCurrentSubscription($this->nextSubscription);
            }
            else {
                $this->setCurrentSubscription(null);
            }
            return true;
        }
        return false;
    }
}
