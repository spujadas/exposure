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

use Exposure\Model\ModerationStatus,
    Exposure\Model\ApprovableMultiLanguageString,
    Exposure\Model\ApprovableLabelledImage,
    Sociable\Model\LabelledImage,
    Sociable\Utility\StringException,
    Sociable\Utility\NumberException,
    Sociable\Model\MultiLanguageString,
    Sociable\Model\MultiCurrencyValue,
    Sociable\Model\URLException,
    Sociable\Model\URL,
    Exposure\Model\User,
    Exposure\Model\Project;

abstract class PostActions extends \Sociable\Controller\PostActions {
    const ERROR_FILE_TOO_LARGE = 'file is too large';
    const ERROR_UPLOAD_ERROR = 'upload error';
    const ERROR_INVALID_FILE_TYPE = 'file type is invalid';
    const ERROR_MISSING_FILE = 'missing file';

    protected function setErrorFromException(\Exception $e, $exceptionArray, $useDefault = true) {
        if (array_key_exists('error_messages', $exceptionArray) 
            && array_key_exists($e->getMessage(), $exceptionArray['error_messages'])) {
            $this->errors[$exceptionArray['error_field']] = 
                $exceptionArray['error_messages'][$e->getMessage()];
        }
        elseif ($useDefault) {
            $this->errors[$exceptionArray['error_field']] = 
                $exceptionArray['default_error_message'];
        }
    }

    protected function updateURL($object, $setter, $urlString, 
        $urlStringExceptionArray, $urlUrlExceptionArray, $descriptionString = null,
        $languageCode = null, $descriptionStringExceptionArray = array(), 
        $replaceDescription = true) {
        $url = new URL;
        
        try {
            $url->setUrl($urlString);
            $object->$setter($url);
        }
        catch (StringException $e) {
            $this->setErrorFromException($e, $urlStringExceptionArray);
        }
        catch (URLException $e) {
            $this->setErrorFromException($e, $urlUrlExceptionArray, false);
        }

        if (!is_null($descriptionString)) {
            $this->updateMultiLanguageString($url, 'getDescription', 'setDescription',
                $descriptionString, $languageCode, $descriptionStringExceptionArray,
                $replaceDescription);
        }
        
    }

    protected function updateString($object, $setter, $newString, $stringExceptionArray) {
        try {
            $object->$setter($newString);
        }
        catch (StringException $e) {
            $this->setErrorFromException($e, $stringExceptionArray);
        }
    }

    protected function updateMultiLanguageString($object, $getter, $setter, $newString,
        $languageCode, $stringExceptionArray, $replace = false) {
        // if new string is identical to current, stop here
        if (!is_null($object->$getter()) 
            && ($object->$getter()->getStringByLanguageCode($languageCode) == $newString)) {
            return false;
        }
        
        // new string is...
        try {
            // ... either new, or replacement required: create a new MLS
            if (is_null($object->$getter()) || $replace) {
                $object->$setter(new MultiLanguageString($newString, $languageCode));
            }
            // ... or different: update
            else {
                $object->$getter()->addStringByLanguageCode($newString, $languageCode, true);
            }
        }
        catch (StringException $e) {
            $this->setErrorFromException($e, $stringExceptionArray);
        }
        
        return true;
    }

    protected function updateMultiCurrencyValue($object, $getter, $setter, $newValue,
        $currencyCode, $numberExceptionArray, $replace = false) {
        // if new value is identical to current, stop here
        if (!is_null($object->$getter()) 
            && ($object->$getter()->getValueByCurrencyCode($currencyCode) == $newValue)) {
            return false;
        }
        
        // new value is...
        try {
            // ... either new, or replacement required: create a new MCV
            if (is_null($object->$getter()) || $replace) {
                $object->$setter(new MultiCurrencyValue($newValue, $currencyCode));
            }
            // ... or different: update
            else {
                $object->$getter()->addValueByCurrencyCode($newValue, $currencyCode, true);
            }
        }
        catch (NumberException $e) {
            $this->setErrorFromException($e, $numberExceptionArray);
        }
        
        return true;
    }

    protected function updateApprovableMultiLanguageString($object, $getter, $setter,
        $newString, $languageCode, $stringExceptionArray, $replace = false) {
        // initialise AMLS field if undefined
        if (is_null($object->$getter())) {
            $newField = new ApprovableMultiLanguageString;
            $moderationStatus = new ModerationStatus;
            $moderationStatus->setStatus(ModerationStatus::STATUS_USER_EDIT);
            $newField->setModerationStatus($moderationStatus);
        }
        // otherwise get existing AMLS field
        else {
            $newField = $object->$getter();
            // keep copy of current string (or clone if current string is to be kept)
            $currentString = $replace?$newField->getCurrent():clone $newField->getCurrent();
        }

        // update field and stop here if nothing changed
        if (!$this->updateMultiLanguageString($newField, 'getCurrent', 'setCurrent',
            $newString, $languageCode, $stringExceptionArray, $replace)) {
            return false;
        }

        // if modified and was previously approved, move last approved value to previous
        if ($newField->getModerationStatus()->getStatus() == ModerationStatus::STATUS_APPROVED) {
            $newField->setPrevious($currentString);
        }

        // back to user edit status
        $newField->getModerationStatus()->setStatus(ModerationStatus::STATUS_USER_EDIT);
        
        // assign to object property
        try {
            $object->$setter($newField);
        }
        catch (StringException $e) {
            $this->setErrorFromException($e, $stringExceptionArray);
        }

        return true;
    }

    protected function updatePlaceAsLocation($object, $setter, $label, $errorArray) {
        $location = $this->getByLabel('Sociable\Model\Location', $label);
        if (is_null($location)) {
            $this->errors[$errorArray['error_field']] = 
                    $errorArray['error_message'];
        }
        else {
            $object->$setter($location);
        }
    }

    protected function updatePlaceAsCountry($object, $setter, $code, $errorArray) {
        $country = $this->getByCode('Sociable\Model\Country', $code);
        if (is_null($country)) {
            $this->errors[$errorArray['error_field']] = 
                    $errorArray['error_message'];
        }
        else {
            $object->$setter($country);
        }
    }

    /* Sample $fileErrorArray for updateLabelledImage:
    $fileErrorArray =  array (
        'error_field' => 'photo',
        'error_messages' => array (
            PostActions::ERROR_FILE_TOO_LARGE => 'file is too large', // $this->translate->_('*.error.fileTooLarge');
            PostActions::ERROR_UPLOAD_ERROR => 'upload error'; // $this->translate->_('*.error.uploadError');
            PostActions::ERROR_INVALID_FILE_TYPE => 'file type is invalid'; // $this->translate->_('*.error.photoInvalidFileType');
        ),
    );
    */

    protected function updateLabelledImage (LabelledImage $labelledImage,
        $errorCode, $filePath, $fileSize, 
        $maxFileSize, $fileErrorArray, $descriptionString, $languageCode, 
        $descriptionStringExceptionArray) {
        
        $changed = false;

        $changed |= $this->updateLabelledImageFile($labelledImage, 
            $errorCode, $filePath, $fileSize, $maxFileSize, $fileErrorArray);
        
        $changed |= $this->updateMultiLanguageString($labelledImage, 'getDescription', 
            'setDescription', $descriptionString, $languageCode, 
            $descriptionStringExceptionArray, true);

        return $changed;
    }

    protected function initLabelledImage (LabelledImage $labelledImage,
        $errorCode, $filePath, $fileSize, 
        $maxFileSize, $fileErrorArray, $descriptionString, $languageCode, 
        $descriptionStringExceptionArray) {
        
        $updated = true;

        $updated &= $this->updateLabelledImageFile($labelledImage, 
            $errorCode, $filePath, $fileSize, $maxFileSize, $fileErrorArray, true);
        
        $updated &= $this->updateMultiLanguageString($labelledImage, 'getDescription', 
            'setDescription', $descriptionString, $languageCode, 
            $descriptionStringExceptionArray, true);

        return $updated;
    }

    protected function updateLabelledImageFile(LabelledImage $labelledImage,
        $errorCode, $filePath, $fileSize, $maxFileSize, $fileErrorArray, $requireUpload = false) {
        if (!$this->wasUploadSuccessful($errorCode, $fileErrorArray, $requireUpload)) { return false; }

        // file size (server-side model)
        if ($fileSize > $maxFileSize) {
            $this->errors[$fileErrorArray['error_field']] 
                = $fileErrorArray['error_messages'][PostActions::ERROR_FILE_TOO_LARGE];
            return false;
        }

        // valid MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        if (!in_array($mimetype, array(
            LabelledImage::MIME_GIF,
            LabelledImage::MIME_JPEG,
            LabelledImage::MIME_PNG,
            ))) {
            $this->errors[$fileErrorArray['error_field']] 
                = $fileErrorArray['error_messages'][PostActions::ERROR_INVALID_FILE_TYPE];
            return false;
        }
        
        $labelledImage->setMime($mimetype);
        $labelledImage->setImageFile($filePath);

        return true;
    }

    protected function wasUploadSuccessful($errorCode, $fileErrorArray, $requireUpload = false) {
        switch ($errorCode) {
        case UPLOAD_ERR_OK:
            return true;
        case UPLOAD_ERR_NO_FILE:
            if ($requireUpload) {
                $this->errors[$fileErrorArray['error_field']] 
                    = $fileErrorArray['error_messages'][PostActions::ERROR_MISSING_FILE];
            }
            break;
        case UPLOAD_ERR_FORM_SIZE:
        case UPLOAD_ERR_INI_SIZE:
            $this->errors[$fileErrorArray['error_field']] 
                = $fileErrorArray['error_messages'][PostActions::ERROR_FILE_TOO_LARGE];
            break;
        default:
            $this->errors[$fileErrorArray['error_field']] 
                = $fileErrorArray['error_messages'][PostActions::ERROR_UPLOAD_ERROR];
            break;
        }
        return false;
    }

    protected function insertLabelledImageInObject($object, $setter,
        $errorCode, $filePath, $fileSize, 
        $maxFileSize, $fileErrorArray, $descriptionString,
        $languageCode, $descriptionStringExceptionArray) {
        $labelledImage = new LabelledImage;

        // init
        if (!$this->initLabelledImage($labelledImage,
            $errorCode, $filePath, $fileSize, $maxFileSize, $fileErrorArray,
            $descriptionString, $languageCode, $descriptionStringExceptionArray)) {
            return false;
        }

        // persist and attach to object
        $this->config->getDocumentManager()->persist($labelledImage);
        $object->$setter($labelledImage);

        return true;
    }

    protected function upsertLabelledImageInObject($object, $getter, $setter,
        $errorCode, $filePath, $fileSize, 
        $maxFileSize, $fileErrorArray, $descriptionString,
        $languageCode, $descriptionStringExceptionArray) {
        // if nothing here yet create a new LI
        if (is_null($object->$getter())) {
            return $this->insertLabelledImageInObject($object, $setter,
                $errorCode, $filePath, $fileSize, 
                $maxFileSize, $fileErrorArray, $descriptionString,
                $languageCode, $descriptionStringExceptionArray);
        }

        // otherwise update LI
        return $this->updateLabelledImage($object->$getter(),
            $errorCode, $filePath, $fileSize, $maxFileSize, $fileErrorArray,
            $descriptionString, $languageCode, $descriptionStringExceptionArray);
    }

    protected function initNewApprovableLabelledImage() {
        $approvableLabelledImage = new ApprovableLabelledImage;
        $moderationStatus = new ModerationStatus;
        $moderationStatus->setStatus(ModerationStatus::STATUS_USER_EDIT);
        $approvableLabelledImage->setModerationStatus($moderationStatus);
        return $approvableLabelledImage;
    }

    protected function updateApprovableLabelledImageFile(ApprovableLabelledImage $approvableLabelledImage,
        $errorCode, $filePath, $fileSize, $maxFileSize, $fileErrorArray) {

        // set aside a copy of the current LI field
        $currentLabelledImage = $approvableLabelledImage->getCurrent();

        // update field and stop here if nothing changed
        if (!$this->updateLabelledImageFile($approvableLabelledImage->getCurrent(),
            $errorCode, $filePath, $fileSize, $maxFileSize, $fileErrorArray)) {
            return false;
        }

        // if modified and was previously approved, move last approved value to previous
        if ($approvableLabelledImage->getModerationStatus()->getStatus() == ModerationStatus::STATUS_APPROVED) {
            $approvableLabelledImage->setPrevious($currentLabelledImage);
        }

        // back to user edit status
        $approvableLabelledImage->getModerationStatus()->setStatus(ModerationStatus::STATUS_USER_EDIT);
        
        return true;
    }

    protected function updateApprovableLabelledImageDescription(ApprovableLabelledImage $approvableLabelledImage, 
        $descriptionString, $languageCode, $descriptionStringExceptionArray) {
        // set aside a copy of the current LI field
        $currentLabelledImage = $approvableLabelledImage->getCurrent();

        // update description and stop here if nothing changed
        if (!$this->updateMultiLanguageString($approvableLabelledImage->getCurrent(), 
            'getDescription', 'setDescription', 
            $descriptionString, $languageCode, $descriptionStringExceptionArray)) {
            return false;
        }

        // if modified and was previously approved, move last approved value to previous
        if ($approvableLabelledImage->getModerationStatus()->getStatus() == ModerationStatus::STATUS_APPROVED) {
            $approvableLabelledImage->setPrevious($currentLabelledImage);
        }

        // back to user edit status
        $approvableLabelledImage->getModerationStatus()->setStatus(ModerationStatus::STATUS_USER_EDIT);

        return true;
    }

    /* Sample $adderErrorArray:
    $adderErrorArray = array(
        'error_field' => 'photo_temp',
        'error_messages' => array (
        PostActions::ERROR_TOO_MANY_APPROVABLE_LABELLED_IMAGES => 'too many images', // $this->translate->_('*.error.tooManyImages');
        ),
    );
    */

    const ERROR_TOO_MANY_APPROVABLE_LABELLED_IMAGES = 'too many approvable labelled images';

    protected function insertApprovableLabelledImageInArrayCollection($object, 
        $getter, $adder, $maxNumberInArray, $adderErrorArray, 
        $errorCode, $filePath, $fileSize, $maxFileSize, $fileErrorArray, $descriptionString, 
        $languageCode, $descriptionStringExceptionArray) {
        $approvableLabelledImage = $this->initNewApprovableLabelledImage();

        // add LI and stop here if failed
        if (!$this->upsertLabelledImageInObject($approvableLabelledImage, 'getCurrent', 'setCurrent',
            $errorCode, $filePath, $fileSize, $maxFileSize, $fileErrorArray, $descriptionString, $languageCode,
            $descriptionStringExceptionArray, true)) { return null; }

        // check if array can support one more ALI
        if ($object->$getter()->count() >= $maxNumberInArray) {
            $this->errors[$adderErrorArray['error_field']] 
                = $adderErrorArray['error_messages'][PostActions::ERROR_TOO_MANY_APPROVABLE_LABELLED_IMAGES];
            return null;
        }

        $object->$adder($approvableLabelledImage);
        $this->config->getDocumentManager()->persist($approvableLabelledImage);
        return $approvableLabelledImage;
    }

    protected function updateApprovableLabelledImageFileInArrayCollection($object, 
        $id, $getter, $errorCode, $filePath, $fileSize, $maxFileSize, $fileErrorArray) {
        $currentApprovableLabelledImage = null;
        foreach ($object->$getter() as $approvableLabelledImage) {
            if ($approvableLabelledImage->getId() == $id) {
                $currentApprovableLabelledImage = $approvableLabelledImage;
                break;
            }
        }

        // stop here if current ALI not found
        if (is_null($currentApprovableLabelledImage)) { return false; }
    
        // update ALI and stop here if no change
        return $this->updateApprovableLabelledImageFile($currentApprovableLabelledImage,
            $errorCode, $filePath, $fileSize, $maxFileSize, $fileErrorArray);
    }

    protected function updateApprovableLabelledImageDescriptionInArrayCollection(
        $object, $id, $getter, 
        $descriptionString, $languageCode, $descriptionStringExceptionArray) {
        $currentApprovableLabelledImage = null;
        foreach ($object->$getter() as $approvableLabelledImage) {
            if ($approvableLabelledImage->getId() == $id) {
                $currentApprovableLabelledImage = $approvableLabelledImage;
                break;
            }
        }

        // stop here if current ALI not found
        if (is_null($currentApprovableLabelledImage)) { return false; }
    
        // update description
        return $this->updateApprovableLabelledImageDescription($currentApprovableLabelledImage, 
            $descriptionString, $languageCode, $descriptionStringExceptionArray);
    }

    protected function updateLabelledImageDescriptionInArrayCollection(
        $object, $id, $getter,
        $descriptionString, $languageCode, $descriptionStringExceptionArray) {
        $currentLabelledImage = null;
        foreach ($object->$getter() as $labelledImage) {
            if ($labelledImage->getId() == $id) {
                $currentLabelledImage = $labelledImage;
                break;
            }
        }

        // stop here if current ALI not found
        if (is_null($currentLabelledImage)) { return false; }
    
        // update description
        return $this->updateMultiLanguageString($labelledImage, 
            'getDescription', 'setDescription', 
            $descriptionString, $languageCode, $descriptionStringExceptionArray);
    }

    const ERROR_TOO_MANY_LABELLED_IMAGES = 'too many approvable labelled images';

    protected function insertLabelledImageInArrayCollection($object, 
        $getter, $adder, $maxNumberInArray, $adderErrorArray, 
        $errorCode, $filePath, $fileSize, 
        $maxFileSize, $fileErrorArray, $descriptionString, 
        $languageCode, $descriptionStringExceptionArray) {
        $labelledImage = new LabelledImage;
        if (!$this->updateLabelledImage($labelledImage,
            $errorCode, $filePath, $fileSize, $maxFileSize, $fileErrorArray,
            $descriptionString, $languageCode, $descriptionStringExceptionArray)) {
            return null;
        }
        $this->config->getDocumentManager()->persist($labelledImage);

        // check if array can support one more ALI
        if ($object->$getter()->count() >= $maxNumberInArray) {
            $this->errors[$adderErrorArray['error_field']] 
                = $adderErrorArray['error_messages'][PostActions::ERROR_TOO_MANY_LABELLED_IMAGES];
            return null;
        }

        $object->$adder($labelledImage);
        return $labelledImage;
    }

    protected function updateLabelledImageInArrayCollection($object, 
        $id, $getter, 
        $errorCode, $filePath, $fileSize, 
        $maxFileSize, $fileErrorArray, $descriptionString, 
        $languageCode, $descriptionStringExceptionArray) {
        $currentLabelledImage = null;
        foreach ($object->$getter() as $labelledImage) {
            if ($labelledImage->getId() == $id) {
                $currentLabelledImage = $labelledImage;
                break;
            }
        }

        // stop here if current LI not found
        if (is_null($currentLabelledImage)) { return false; }

        return $this->updateLabelledImage($currentLabelledImage,
            $errorCode, $filePath, $fileSize, $maxFileSize, $fileErrorArray,
            $descriptionString, $languageCode, $descriptionStringExceptionArray);
    }

    protected function updateLabelledImageFileInArrayCollection($object, 
        $id, $getter, 
        $errorCode, $filePath, $fileSize, 
        $maxFileSize, $fileErrorArray) {
        $currentLabelledImage = null;
        foreach ($object->$getter() as $labelledImage) {
            if ($labelledImage->getId() == $id) {
                $currentLabelledImage = $labelledImage;
                break;
            }
        }

        // stop here if current LI not found
        if (is_null($currentLabelledImage)) { return false; }

        return $this->updateLabelledImageFile($currentLabelledImage,
            $errorCode, $filePath, $fileSize, $maxFileSize, $fileErrorArray);
    }

    protected function removeElementByIdFromArrayCollection($object, 
        $id, $getter, $alsoRemoveElement = true) {
        foreach ($object->$getter() as $element) {
            if ($element->getId() == $id) {
                $object->$getter()->removeElement($element);
                if ($alsoRemoveElement) {
                    $this->config->getDocumentManager()->remove($element);
                }
                return true;
            }
        }
        return false;
    }

    protected function canSignedInUserEditProject(Project $project) {
        if (!isset($_SESSION['user']['id'])) { return false; }

        // get current user
        $user = $this->getById('Exposure\Model\User', $_SESSION['user']['id']);

        if (is_null($user)) { return false; }

        // check if user owns project and if project is editable
        if (!$user->ownsProject($project) || !$project->isEditable()) { 
            return false;            
        }

        return true;
    }

    protected function sendEmail($emailTemplate, $parameters, User $recipient) {
        $subject  = $emailTemplate->renderBlock('subject',   $parameters);
        $bodyHtml = $emailTemplate->renderBlock('body_html', $parameters);
        $bodyText = $emailTemplate->renderBlock('body_text', $parameters);
        
        $message = \Swift_Message::newInstance()
            ->setFrom(array(
                $this->config->getParam('emailFromAddress') 
                    => $this->config->getParam('emailFromName'))
                )
            ->setSubject($subject)
            ->setBody($bodyText, 'text/plain')
            ->addPart($bodyHtml, 'text/html');
        
        $message->setTo($recipient->getEmail());
        return $this->config->getSwiftMailer()->send($message);
    }

    protected function getSignedInUser() {
        if (!isset($_SESSION['user']['id'])) { return null; }
        return $this->getById('Exposure\Model\User', $_SESSION['user']['id']);
    }

    protected function updateStatusAndNotify($objectGetter, 
        $currentStatus, $updatedStatus, $notifier) {
        header('Content-Type: application/json');

        // get object to update
        if (is_null($object = $this->$objectGetter())
            || ($object->getStatus() != $currentStatus)) {
            echo json_encode(false);
            return;
        }

        // update return
        $object->setStatus($updatedStatus);
        $this->config->getDocumentManager()->flush();

        // notify and send email as req'd
        $this->$notifier($object);

        echo json_encode(true);
    }
}


