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

use Sociable\Model\Organisation,
    Sociable\Utility\StringValidator,
    Sociable\Model\MultiLanguageString,
    Sociable\Model\LabelledImage,
    Sociable\Model\ContactDetails,
    Sociable\Model\WebPresence,
    Sociable\Utility\SEO;

use Doctrine\Common\Collections\ArrayCollection;

class SponsorOrganisation extends Organisation {
    protected $urlSlug = null;
    const EXCEPTION_MISSING_URL_SLUG = 'missing URL slug';

    /** @var \DateTime */
    protected $creationDateTime = null;
    const EXCEPTION_INVALID_CREATION_DATE_TIME = 'invalid creation date time';
    
    /** @var MultiLanguageString */
    protected $description = null;
    const DESCRIPTION_MAX_LENGTH = 1400;
    const EXCEPTION_INVALID_DESCRIPTION = 'invalid description';
    
    /** @var LabelledImage */
    protected $logo = null;
    const EXCEPTION_INVALID_LOGO = 'invalid logo';
    const LOGO_MAX_SIZE = 2097152; // 2 Mo

    /** @var ContactDetails */
    protected $contactDetails = null;
    const EXCEPTION_INVALID_CONTACT_DETAILS = 'invalid contact details';
    
    /** @var ArrayCollection of WebPresence*/
    protected $webPresences;
    const WEB_PRESENCES_MAX_COUNT = 10;
    const EXCEPTION_INVALID_WEB_PRESENCE = 'invalid web presence';
    const EXCEPTION_TOO_MANY_WEB_PRESENCES = 'too many web presences';
    
    /** @var ArrayCollection of Theme */
    protected $soughtThemes;
    const EXCEPTION_INVALID_SOUGHT_THEME = 'invalid sought theme';
    
    /** @var SponsorContributionTypes */
    protected $soughtContributionTypes = null;
    const EXCEPTION_INVALID_SOUGHT_CONTRIBUTION_TYPES = 'invalid contribution types';
    
    /** @var ArrayCollection of SponsorReturnType */
    protected $soughtSponsorReturnTypes;
    const EXCEPTION_INVALID_SOUGHT_SPONSOR_RETURN_TYPES = 'invalid sponsor return types';
    
    /** @var ArrayCollection of User */
    protected $sponsorUsers;
    const SPONSOR_USERS_MAX_COUNT = 1;
    const EXCEPTION_TOO_MANY_SPONSOR_USERS = 'too many sponsor users';
    const EXCEPTION_NO_SPONSOR_USER = 'no sponsor user';
    const EXCEPTION_INVALID_SPONSOR_USER = 'invalid sponsor user';
    const EXCEPTION_OBJECT_USER_NOT_A_SPONSOR = 'object user not a sponsor';
    const EXCEPTION_DUPLICATE_SPONSOR_USER = 'duplicate sponsor user';

    /** @var ArrayCollection of SponsorContributionNotification,
        CommentNotification, SponsorReturnNotification */
    protected $notifications;
    const EXCEPTION_INVALID_NOTIFICATION_TYPE = 'invalid notification type';
    
    /** @var ArrayCollection of ProjectWant */
    protected $wants; // inverse side
    
    /** @var ArrayCollection of SponsorContribution */
    protected $contributions; // inverse side
    
    /** @var ArrayCollection of Comment */
    protected $comments;
    
    public function __construct() {
        $this->webPresences = new ArrayCollection;
        $this->soughtThemes = new ArrayCollection;
        $this->soughtSponsorReturnTypes = new ArrayCollection;
        $this->sponsorUsers = new ArrayCollection;
        $this->notifications = new ArrayCollection;
        $this->wants = new ArrayCollection;
        $this->contributions = new ArrayCollection;
        $this->comments = new ArrayCollection;
    }
    
    public function setName($name) {
        parent::setName($name);
        $this->generateUrlSlug($name);
    }
    
    public function generateUrlSlug() {
        $this->urlSlug = SEO::generateSlug($this->name);
    }
    
    public function getUrlSlug() {
        return $this->urlSlug;
    }
    
    protected function validateUrlSlug($urlSlug) {
        SEO::validateUrlSlug($urlSlug);
    }

    public function setCreationDateTime(\DateTime $datetime) {
        $this->creationDateTime = $datetime;
        return $this->creationDateTime;
    }

    public function getCreationDateTime() {
        return $this->creationDateTime;
    }
    
    public function setDescription(MultiLanguageString $description) {
        try {
            $this->validateDescription($description);
        } catch (Exception $e) {
            $this->description = null;
            throw $e;
        }
        $this->description = $description;
        return $this->description;
        
    }
    
    protected function validateDescription(MultiLanguageString $description) {
        $description->validate(array(
            'not_empty' => true,
            'max_length' => self::DESCRIPTION_MAX_LENGTH));
    }

    public function getDescription() {
        return $this->description;
    }

    public function setLogo (LabelledImage $logo) {
        try {
            $this->validateLogo($logo);
        }
        catch (Exception $e) {
            $this->logo = null;
            throw $e;
        }
        $this->logo = $logo;
        return $this->logo;
    }
    
    public function getLogo () {
        return $this->logo;
    }

    protected function validateLogo (LabelledImage $logo) {
        $logo->validate();
    }
    
    public function getContactDetails() {
        return $this->contactDetails;
    }

    public function setContactDetails(ContactDetails $contactDetails = null) {
        if (!is_null($contactDetails)) {
            try {
                $this->validateContactDetails($contactDetails);
            }
            catch (Exception $e) {
                $this->contactDetails = null;
                throw $e;
            }
        }
        $this->contactDetails = $contactDetails;
        return $this->contactDetails;
    }
    
    public function validateContactDetails(ContactDetails $contactDetails) {
        $contactDetails->validate();
    }
    
    public function addWebPresence(WebPresence $webPresence) {
        if ($this->webPresences->count() == self::WEB_PRESENCES_MAX_COUNT) {
            throw new SponsorOrganisationException(self::EXCEPTION_TOO_MANY_WEB_PRESENCES);
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

    public function addSoughtTheme(Theme $soughtTheme) {
        $this->soughtThemes[] = $soughtTheme;
    }
    
    public function removeSoughtTheme(Theme $soughtTheme) {
        return $this->soughtThemes->removeElement($soughtTheme);
    }
    
    public function getSoughtThemes() {
        return $this->soughtThemes;
    }

    public function resetSoughtThemes() {
        $this->soughtThemes = new ArrayCollection;
    }
    
    public function getSoughtContributionTypes() {
        return $this->soughtContributionTypes;
    }

    public function setSoughtContributionTypes(SponsorContributionTypes $soughtContributionTypes) {
        $this->soughtContributionTypes = $soughtContributionTypes;
        return $this->soughtContributionTypes;
    }

    public function addSoughtSponsorReturnType(SponsorReturnType $soughtSponsorReturnType) {
        $this->soughtSponsorReturnTypes[] = $soughtSponsorReturnType;
    }
    
    public function removeSoughtSponsorReturnType(SponsorReturnType $soughtSponsorReturnType) {
        return $this->soughtSponsorReturnTypes->removeElement($soughtSponsorReturnType);
    }
    
    public function getSoughtSponsorReturnTypes() {
        return $this->soughtSponsorReturnTypes;
    }
    
    public function getSponsorUsers() {
        return $this->sponsorUsers;
    }

    public function addSponsorUser(User $sponsorUser) {
        $this->validateSponsorUser($sponsorUser);
        if ($this->sponsorUsers->count() == self::SPONSOR_USERS_MAX_COUNT) {
            throw new SponsorOrganisationException(self::EXCEPTION_TOO_MANY_SPONSOR_USERS);
        }
        if ($sponsorUser->getSponsorOrganisations()->count() >= User::SPONSOR_ORGANISATIONS_MAX_NUMBER) {
            throw new UserException(User::EXCEPTION_TOO_MANY_SPONSOR_ORGANISATIONS);
        }
        if ($this->existsSponsorUserByEmail($sponsorUser->getEmail())) {
            throw new SponsorOrganisationException(self::EXCEPTION_DUPLICATE_SPONSOR_USER);
        }

        $this->sponsorUsers[] = $sponsorUser;
    }
    
    protected function existsSponsorUserByEmail($email) {
        foreach ($this->sponsorUsers as $sponsorUser) {
            if ($sponsorUser->getEmail() == $email) {
                return true;
            }
        }
        return false;
    }
    
    public function hasMember(User $user) {
        return $this->sponsorUsers->contains($user);
    }
    
    public function removeSponsorUser(User $sponsorUser) {
        return $this->sponsorUsers->removeElement($sponsorUser);
    }
    
    protected function validateSponsorUser(User $sponsorUser) {
        if ($sponsorUser->getType() != User::TYPE_SPONSOR) {
            throw new SponsorOrganisationException(self::EXCEPTION_OBJECT_USER_NOT_A_SPONSOR);
        }
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
            Notification::TYPE_COMMENT,
            Notification::TYPE_SPONSOR_CONTRIBUTION,
            Notification::TYPE_SPONSOR_RETURN))) {
            throw new SponsorOrganisationException(self::EXCEPTION_INVALID_NOTIFICATION_TYPE);
        }
    }

    public function getWants() {
        return $this->wants;
    }

    public function wantsProject(Project $project) {
        foreach ($this->wants as $want) {
            if ($want->getProject() == $project) {
                return true;
            }
        }
        return false;
    }

    public function getComments() {
        return $this->comments;
    }

    public function getContributions() {
        return $this->contributions;
    }

    public function validate() {
        parent::validate();
        if (is_null($this->urlSlug)) {
            throw new SponsorOrganisationException(self::EXCEPTION_MISSING_URL_SLUG);
        }
        $this->validateUrlSlug($this->urlSlug);
        if (!is_a($this->description, 'Sociable\Model\MultiLanguageString')) {
            throw new SponsorOrganisationException(self::EXCEPTION_INVALID_DESCRIPTION);
        }
        if (!is_a($this->creationDateTime, 'DateTime')) {
            throw new SponsorOrganisationException(self::EXCEPTION_INVALID_CREATION_DATE_TIME);
        }
        $this->validateDescription($this->description);
        if (!is_a($this->logo, 'Sociable\Model\LabelledImage')) {
            throw new SponsorOrganisationException(self::EXCEPTION_INVALID_LOGO);
        }
        $this->validateLogo($this->logo);
        if (!is_null($this->contactDetails)) {
            if (!is_a($this->contactDetails, 'Sociable\Model\ContactDetails')) {
                throw new SponsorOrganisationException(self::EXCEPTION_INVALID_CONTACT_DETAILS);
            }
        }
        foreach ($this->webPresences as $webPresence) {
            if (!is_a($webPresence, 'Sociable\Model\WebPresence')) {
                throw new SponsorOrganisationException(self::EXCEPTION_INVALID_WEB_PRESENCE);
            }
        }
        foreach ($this->soughtThemes as $soughtTheme) {
            if (!is_a($soughtTheme, 'Exposure\Model\Theme')) {
                throw new SponsorOrganisationException(self::EXCEPTION_INVALID_SOUGHT_THEME);
            }
        }
        if (!is_a($this->soughtContributionTypes, 'Exposure\Model\SponsorContributionTypes')) {
            throw new SponsorOrganisationException(self::EXCEPTION_INVALID_SOUGHT_CONTRIBUTION_TYPES);
        }
        foreach ($this->soughtSponsorReturnTypes as $soughtSponsorReturnType) {
            if (!is_a($soughtSponsorReturnType, 'Exposure\Model\SponsorReturnType')) {
                throw new SponsorOrganisationException(self::EXCEPTION_INVALID_SOUGHT_SPONSOR_RETURN_TYPES);
            }
        }
        foreach ($this->sponsorUsers as $sponsorUser) {
            if (!is_a($sponsorUser, 'Exposure\Model\User')) {
                throw new SponsorOrganisationException(self::EXCEPTION_INVALID_SPONSOR_USER);
            }
        }
        if ($this->sponsorUsers->count() == 0) {
            throw new SponsorOrganisationException(self::EXCEPTION_NO_SPONSOR_USER);
        }
        if ($this->sponsorUsers->count() > self::SPONSOR_USERS_MAX_COUNT) {
            throw new SponsorOrganisationException(self::EXCEPTION_TOO_MANY_SPONSOR_USERS);
        }
        foreach ($this->notifications as $notification) {
            $this->validateNotification($notification);
        }
    }
}


