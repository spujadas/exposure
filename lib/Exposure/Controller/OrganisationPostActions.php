<?php

/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

namespace Exposure\Controller;

use Exposure\Model\SponsorOrganisation,
    Exposure\Model\User,
    Sociable\Model\URL,
    Sociable\Model\WebPresence,
    Sociable\Model\ContactDetails,
    Sociable\Utility\StringValidator;

class OrganisationPostActions extends \Exposure\Controller\PostActions {
    protected $errors;
    protected $autofill;

    protected function saveIsValidPost() {
        if (!$this->postHasIndices(array('name', 'type', 'business_sector', 
            'description', 'email', 'phone_number', 'mobile_number', 
            'fax_number', 'skype_name', 'web_presence_urls', 
            'web_presence_descriptions'))) {
            return false;
        }
        
        if ((count($_POST['web_presence_urls']) != count($_POST['web_presence_descriptions'])) 
            || (count($_POST['web_presence_urls']) > SponsorOrganisation::WEB_PRESENCES_MAX_COUNT)) {
            return false;
        }
        return true;
    }

    protected function isSlugUsable($currentSlug, $newSlug) {
        // no slug change => remains available
        if ($currentSlug == $newSlug) {
            return true;
        }

        // otherwise get organisation by new slug
        $organisation = $this->getByUrlSlug('Exposure\Model\SponsorOrganisation', $newSlug);

        // slug unused => authorised
        if (is_null($organisation)) {
            return true;
        }

        // otherwise not authorised
        return false;
        
    }

    protected function updateName(SponsorOrganisation $organisation, $name) {
        $currentSlug = $organisation->getUrlSlug();

        $stringExceptionArray = array (
            'error_field' => 'name',
            'error_messages' => array (
                StringValidator::EXCEPTION_EMPTY => 'name is missing', // $this->translate->_('organisationSave.error.emptyName'),
                StringValidator::EXCEPTION_TOO_LONG => 'this name is too long',  // $this->translate->_('organisationSave.error.nameTooLong'),
            ),
            'default_error_message' => 'this name is invalid', // $this->translate->_('organisationSave.error.invalidName'),
        );
        $this->updateString($organisation, 'setName', $name, $stringExceptionArray);

        if (!$this->isSlugUsable($currentSlug, $organisation->getUrlSlug())) {
            $this->errors['name'] = 'this name is already in use'; // $this->translate->_('organisationSave.error.nameUnavailable'),
        }

        $this->autofill['name'] = $name;
    }

    protected function updateType(SponsorOrganisation $organisation, $type) {
        try {
            $organisation->setType($type);
        }
        catch (\Exception $e) {
            $this->errors['type'] = 'type is invalid'; // $this->translate->_('organisationSave.error.invalidType'),
        }

        $this->autofill['type'] = $type;
    }

    protected function updateBusinessSector(SponsorOrganisation $organisation, 
        $businessSectorCode) {
        $businessSector = $this->getByCode('Sociable\Model\BusinessSector', $businessSectorCode);
        if (is_null($businessSector)) {
            $this->errors['business_sector'] = 'business sector is invalid';
                // $this->translate->_('organisationSave.error.invalidBusinessSector');
        }
        else {
            $organisation->setBusinessSector($businessSector);
            $this->autofill['business_sector'] = $businessSectorCode;
        }
    }

    protected function updateDescription(SponsorOrganisation $organisation, 
        $description, $languageCode) {
        $stringExceptionArray = array (
            'error_field' => 'description',
            'error_messages' => array (
                StringValidator::EXCEPTION_EMPTY => 'description missing', // $this->translate->_('organisationSave.error.emptyDescription'),
                StringValidator::EXCEPTION_TOO_LONG => 'this description is too long', // $this->translate->_('organisationSave.error.descriptionTooLong'),
            ),
            'default_error_message' => 'this description is invalid', // $this->translate->_('organisationSave.error.invalidDescription');
        );
        $this->updateMultiLanguageString($organisation, 
            'getDescription', 'setDescription',
            $description, $languageCode, $stringExceptionArray);

        $this->autofill['description'] = $description;
    }

    protected function updateLogo(SponsorOrganisation $organisation, 
        $errorCode, $filePath, $fileSize, 
        $descriptionString, $languageCode) {
        $fileErrorArray = array (
            'error_field' => 'logo',
            'error_messages' => array (
                PostActions::ERROR_MISSING_FILE => 'file missing', // $this->translate->_('organisationSave.error.missingFile');
                PostActions::ERROR_FILE_TOO_LARGE => 'file is too large', // $this->translate->_('organisationSave.error.photoFileTooLarge');
                PostActions::ERROR_UPLOAD_ERROR => 'upload error', // $this->translate->_('organisationSave.error.uploadError');
                PostActions::ERROR_INVALID_FILE_TYPE => 'file type is invalid', // $this->translate->_('organisationSave.error.photoInvalidFileType');
            ),
        );

        $descriptionStringExceptionArray = array (
            'error_field' => 'photo',
            'default_error_message' => 'cannot save this logo', // $this->translate->_('organisationSave.error.cannotSaveLogo');
        );

        // update image and description
        $this->upsertLabelledImageInObject($organisation, 'getLogo', 'setLogo',
            $errorCode, $filePath, $fileSize,
            SponsorOrganisation::LOGO_MAX_SIZE, $fileErrorArray, 
            $descriptionString, $languageCode, $descriptionStringExceptionArray);
    }

    protected function updateContactDetails(SponsorOrganisation $organisation, 
        $email, $phoneNumber, $mobileNumber, $faxNumber, $skypeName) {
        $atLeastOneContactDetail = false;
        foreach (array($email, $phoneNumber, $mobileNumber, $faxNumber, 
            $skypeName) as $contactDetail) {
            if (!empty($contactDetail)) {
                $atLeastOneContactDetail = true;
                break;
            }
        }
        if (!$atLeastOneContactDetail) {
            $this->errors['contact_details'] = 'at least one contact field must be filled in';
                // $this->translate->_('organisationSave.error.missingContactDetails');
            return;
        }
        $this->updateEmail($organisation, $email);
        $this->updatePhoneNumber($organisation, $phoneNumber);
        $this->updateMobileNumber($organisation, $mobileNumber);
        $this->updateFaxNumber($organisation, $faxNumber);
        $this->updateSkypeName($organisation, $skypeName);
    }

    protected function updateEmail(SponsorOrganisation $organisation, $email) {
        // valid email
        try {
            $organisation->getContactDetails()->setEmail(empty($email)?null:$email);
        }
        catch (\Exception $e) {
            $this->errors['email'] = 'this email address is invalid';
                // $this->translate->_('organisationSave.error.invalidEmail');
        }

        $this->autofill['email'] = $email;
    }

    protected function updatePhoneNumber(SponsorOrganisation $organisation, $phoneNumber) {
        try {
            $organisation->getContactDetails()->setPhoneNumber(empty($phoneNumber)?null:$phoneNumber);
        }
        catch (\Exception $e) {
            $this->errors['phone_number'] = 'this phone number is invalid';
                // $this->translate->_('organisationSave.error.invalidPhoneNumber');
        }

        $this->autofill['phone_number'] = $phoneNumber;
    }

    protected function updateMobileNumber(SponsorOrganisation $organisation, $mobileNumber) {
        try {
            $organisation->getContactDetails()->setMobileNumber(empty($mobileNumber)?null:$mobileNumber);
        }
        catch (\Exception $e) {
            $this->errors['mobile_number'] = 'this mobile phone number is invalid';
                // $this->translate->_('organisationSave.error.invalidMobileNumber');
        }
        
        $this->autofill['mobile_number'] = $mobileNumber;
    }

    protected function updateFaxNumber(SponsorOrganisation $organisation, $faxNumber) {
        try {
            $organisation->getContactDetails()->setFaxNumber(empty($faxNumber)?null:$faxNumber);
        }
        catch (\Exception $e) {
            $this->errors['fax_number'] = 'this fax number is invalid';
                // $this->translate->_('organisationSave.error.invalidFaxNumber');
        }

        $this->autofill['fax_number'] = $faxNumber;
    }

    protected function updateSkypeName(SponsorOrganisation $organisation, $skypeName) {
        try {
            $organisation->getContactDetails()->setSkypeName(empty($skypeName)?null:$skypeName);
        }
        catch (\Exception $e) {
            $this->errors['skype_name'] = 'this Skype name is invalid';
                // $this->translate->_('organisationSave.error.invalidSkypeName');
        }

        $this->autofill['skype_name'] = $skypeName;
    }

    protected function updateWebPresences(SponsorOrganisation $organisation, $webPresenceUrls, 
        $webPresenceDescriptions, $languageCode) {
        $this->autofill['web_presences'] = array();
        $this->errors['web_presences'] = array();

        $urlStringExceptionArray = array (
            'error_field' => 'webpresence_url_temp',
            'error_messages' => array (
                StringValidator::EXCEPTION_EMPTY => 'URL is missing',  // $this->translate->_('organisationSave.error.urlEmpty'),
                StringValidator::EXCEPTION_TOO_LONG => 'this URL is too long',  // $this->translate->_('organisationSave.error.urlTooLong'),
            ),
            'default_error_message' => 'this URL is invalid', // $this->translate->_('organisationSave.error.invalidUrl'),
        );
        $urlExceptionArray = array (
            'error_field' => 'webpresence_url_temp',
            'error_messages' => array (
                URL::EXCEPTION_INVALID_URL => 'this URL is invalid',  // $this->translate->_('organisationSave.error.invalidUrl'),
            ),
        );
        $descriptionStringExceptionArray = array (
            'error_field' => 'webpresence_description_temp',
            'error_messages' => array (
                StringValidator::EXCEPTION_TOO_LONG => 'this description is too long',  // $this->translate->_('organisationSave.error.urlDescriptionTooLong'),
            ),
            'default_error_message' => 'this description is invalid', // $this->translate->_('organisationSave.error.invalidUrlDescription'),
        );

        $organisation->resetWebPresences();

        // ignore last web presence if URL and description is empty
        $count = (empty($webPresenceUrls[count($webPresenceUrls)-1])
            && empty($webPresenceDescriptions[count($webPresenceUrls)-1]))
            ?count($webPresenceUrls)-1
            :count($webPresenceUrls);

        // fill in web presences array with each web presence from form
        for ($i=0;$i<$count;$i++) {
            $this->autofill['web_presences'][$i]['url'] = $webPresenceUrls[$i];
            $this->autofill['web_presences'][$i]['description'] = $webPresenceDescriptions[$i];

            $webPresence = new WebPresence;

            $this->updateUrl($webPresence, 'setUrlInferType', $webPresenceUrls[$i], 
                $urlStringExceptionArray, $urlExceptionArray, $webPresenceDescriptions[$i],
                $languageCode, $descriptionStringExceptionArray);
            if (!empty($this->errors['webpresence_url_temp'])) {
                $this->errors['web_presences'][$i]['url'] 
                    = $this->errors['webpresence_url_temp'];
                unset($this->errors['webpresence_url_temp']);
            }
            if (!empty($this->errors['webpresence_description_temp'])) {
                $this->errors['web_presences'][$i]['description'] 
                    = $this->errors['webpresence_description_temp'];
                unset($this->errors['webpresence_description_temp']);
            }
            $organisation->addWebPresence($webPresence);
        }

        // remove from error array if no errors
        if (count($this->errors['web_presences']) == 0) {
            unset($this->errors['web_presences']);
        }
    }

    protected function updateSoughtThemes(SponsorOrganisation $organisation, 
        $themes = array()) {
        $this->autofill['themes'] = array();
        $organisation->resetSoughtThemes();

        if (count($themes) == 0) {
            $this->errors['themes'] = 'at least one theme must be selected';
                    // $this->translate->_('organisationSave.error.missingTheme');
        }

        foreach ($themes as $themeLabel) {
            $theme = $this->getByLabel('Exposure\Model\Theme', $themeLabel);
            if (is_null($theme)) {
                $this->errors['themes'] = 'theme is invalid';
                    // $this->translate->_('organisationSave.error.invalidTheme');
            }
            else {
                $organisation->addSoughtTheme($theme);
                $this->autofill['themes'][] = $themeLabel;
            }
        }
    }

    protected function updateOrganisation(SponsorOrganisation $organisation) {
        $this->autofill = array();
        $this->errors = array();

        // update organisation attributes
        $this->updateName($organisation, $_POST['name']);
        $this->updateType($organisation, $_POST['type']);
        $this->updateBusinessSector($organisation, $_POST['business_sector']);
        $this->updateDescription(
            $organisation, $_POST['description'], $_SESSION['language']);

        if (array_key_exists('logo', $_FILES)) {
            $this->updateLogo($organisation, 
                $_FILES['logo']['error'], $_FILES['logo']['tmp_name'], $_FILES['logo']['size'],
                $_POST['name'], $_SESSION['language']);
        }
        elseif (is_null($organisation->getLogo())) {
            $this->errors['logo'] = 'missing logo';
        }

        $this->updateContactDetails($organisation, $_POST['email'], 
            $_POST['phone_number'], $_POST['mobile_number'],
            $_POST['fax_number'], $_POST['skype_name']);
        $this->updateWebPresences($organisation, $_POST['web_presence_urls'], 
            $_POST['web_presence_descriptions'], $_SESSION['language']);
        if (array_key_exists('themes', $_POST)) {
            $this->updateSoughtThemes($organisation, $_POST['themes']);
        }
        else {
            $this->updateSoughtThemes($organisation);
        }
    }

    public function save() {
        if (!isset($_SESSION['user']['id'])) { return self::NOT_SIGNED_IN; }
        if (!$this->saveIsValidPost()) { return self::INVALID_POST; }
        
        // get current user
        $user = $this->getById('Exposure\Model\User', $_SESSION['user']['id']);
        if (is_null($user)) { return self::INVALID_POST; }


        // either get current organisation...
        if (array_key_exists('organisation_id', $_POST)) {
            $organisation = $this->getById('Exposure\Model\SponsorOrganisation', $_POST['organisation_id']);
            if (is_null($organisation)) { return self::INVALID_POST; }

            if (!$organisation->hasMember($user)) { 
                return self::NOT_AUTHORISED; 
            }
        }
        // ... or create new organisation
        else {
            // check if user is a sponsor
            if ($user->getType() != User::TYPE_SPONSOR) {
                return self::NOT_AUTHORISED; 
            }

            // check if max number of users/organisations not reached
            if ($user->getSponsorOrganisations()->count() >= User::SPONSOR_ORGANISATIONS_MAX_NUMBER) {
                $_SESSION['message'] = array (
                    'content' => 'the maximum number of organisations ('
                        . User::SPONSOR_ORGANISATIONS_MAX_NUMBER .') has been reached',
                    // 'content' => $this->translate->_('organisationSave.error.maxNumberOrganisationsReached'),
                    'type' => 'error');
                return self::NOT_AUTHORISED; 
            }

            $organisation = new SponsorOrganisation;
            $organisation->setCreationDateTime(new \DateTime);
            $this->config->getDocumentManager()->persist($organisation);
            $organisation->addSponsorUser($user);
            $organisation->setContactDetails(new ContactDetails);

            if ($user->getFirstTime() == User::FIRST_TIME_ORGANISATION) {
                $user->setFirstTime(null);
            }
        }

        // update from form data
        $this->updateOrganisation($organisation);

        // in case of errors
        if (!empty($this->errors)) {
            // clear dm, set session errors/autofill/message
            $this->config->getDocumentManager()->clear();
            $_SESSION['errors'] = $this->errors;
            $_SESSION['autofill'] = $this->autofill;
            if (array_key_exists('organisation_id', $_POST)) {
                $_SESSION['request'] = $organisation->getUrlSlug();
            }
            $_SESSION['message'] = array (
             'content' => 'some fields are incorrectly filled in',
             // 'content' => $this->translate->_('organisationSave.error.incorrectFields'),
             'type' => 'error');

            return self::INVALID_DATA;
        }

        $this->config->getDocumentManager()->flush();
        if (array_key_exists('organisation_id', $_POST)) {
            $_SESSION['request'] = $organisation->getUrlSlug();
        }
        $_SESSION['message'] = array (
                'content' => 'organisation saved',
                // 'content' => $this->translate->_('organisationSave.success.organisationSaved'),
                'type' => 'success');

        return self::SUCCESS;
    }
}


