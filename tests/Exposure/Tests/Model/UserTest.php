<?php

/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

namespace Exposure\Tests\Model;

use Exposure\Model\User,
    Sociable\Model\PasswordAuthenticator,
    Sociable\Model\ConfirmationCode,
    Exposure\Model\ModerationStatus,
    Sociable\Utility\StringValidator,
    Exposure\Model\ProfileModerationNotification,
    Exposure\Model\Project,
    Exposure\Model\ProjectThemeSuggestionNotification,
    Sociable\Model\LabelledImage,
    Sociable\Model\Country,
    Sociable\Model\Location,
    Sociable\Model\Address,
    Sociable\Model\Language,
    Sociable\Model\Currency,
    Exposure\Model\Subscription,
    Exposure\Model\ProjectOwnerPreferences,
    Exposure\Model\SponsorUserPreferences,
    Sociable\Model\MultiLanguageString;

class UserTest extends \PHPUnit_Framework_TestCase {
    protected $user;

    // from \Sociable
    const NAME = 'Foo';
    const SURNAME = 'Bar';
    const EMAIL = 'foo@bar.com';
    protected $authenticator;
    
    // from \Exposure
    protected $emailConfirmationCode;
    protected $passwordResetCode;

    const STATUS = User::STATUS_VALIDATED;
    const STATUS_INVALID = 's';
    
    protected $moderationStatus;
    protected $registrationDateTime;

    const FIRST_TIME = User::FIRST_TIME_ORGANISATION;
    const FIRST_TIME_INVALID = 'f';

    const TYPE = User::TYPE_PROJECT_OWNER;
    const TYPE_INVALID = 's';

    protected $ownedProject;
    protected $notification;
    protected $photo;
    protected $country;
    protected $location;
    protected $billingAddress;
    protected $presentation;
    protected $presentation_toolong;
    protected $presentation_empty;
    protected $nextSubscription;
    protected $currentSubscription;
    protected $pastSubscription;
    const LANGUAGE_CODE = 'fr';
    const CURRENCY_CODE = 'EUR';
    protected $bookmarkedProject;
    protected $projectOwnerPreferences;
    protected $sponsorUserPreferences;
    protected $tempDraftProjectPhoto;
    
    public function setUp() {
        $this->user = new User();
        
        $this->authenticator = new PasswordAuthenticator;
        $this->authenticator->setParams(array('password' => '1234'));
        
        $this->emailConfirmationCode = new ConfirmationCode;
        $this->passwordResetCode = new ConfirmationCode;
        
        $this->moderationStatus = new ModerationStatus;
        $this->moderationStatus->setStatus(ModerationStatus::STATUS_APPROVED);
        $this->moderationStatus->setReasonCode('s');
        $this->moderationStatus->setComment('comment');
                
        $this->registrationDateTime = new \DateTime;
        
        $this->ownedProject = new Project;
        $this->notification = new ProfileModerationNotification;
        
        $this->photo = new LabelledImage;
        $this->photo->setImageFile('photo.png');
        $this->photo->setMime(LabelledImage::MIME_PNG);
        $this->photo->setDescription(new MultiLanguageString('chaîne de caractères', 'fr'));
        
        $this->tempDraftProjectPhoto = new LabelledImage;
        $this->tempDraftProjectPhoto->setImageFile('photo.png');
        $this->tempDraftProjectPhoto->setMime(LabelledImage::MIME_PNG);
        $this->tempDraftProjectPhoto->setDescription(new MultiLanguageString('chaîne de caractères', 'fr'));
        
        $this->country = new Country;
        $this->location = new Location;
        
        $this->billingAddress = new Address;
        
        $this->presentation = new MultiLanguageString('foo', 'fr');
        $this->presentation_toolong = new MultiLanguageString(
                str_repeat('a', User::PRESENTATION_MAX_LENGTH + 1),
                'fr');
        $this->presentation_empty = new MultiLanguageString('', 'fr');

        $this->nextSubscription = new Subscription;
        $this->currentSubscription = new Subscription;
        $this->pastSubscription = new Subscription;
        $this->bookmarkedProject = new Project;
        $this->projectOwnerPreferences = new ProjectOwnerPreferences;
        $this->sponsorUserPreferences = new SponsorUserPreferences;
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\User', $this->user);
    }
    
    public function testSetGetEmailConfirmationCode() {
        $this->assertEquals($this->emailConfirmationCode, 
                $this->user->setEmailConfirmationCode($this->emailConfirmationCode));
        $this->assertEquals($this->emailConfirmationCode, 
                $this->user->getEmailConfirmationCode());
    }

    public function testSetGetPasswordResetCode() {
        $this->assertEquals($this->passwordResetCode, 
                $this->user->setPasswordResetCode($this->passwordResetCode));
        $this->assertEquals($this->passwordResetCode, 
                $this->user->getPasswordResetCode());
    }
    
    public function testSetStatus_null() {
        $this->setExpectedException('Exposure\Model\UserException', 
            User::EXCEPTION_INVALID_STATUS);
        $this->user->setStatus(null);
    }
    
    public function testGetStatus_null() {
        try {
            $this->user->setStatus(null);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->user->getStatus());
    }
    
    public function testSetStatus_invalidstatus() {
        $this->setExpectedException('Exposure\Model\UserException', 
            User::EXCEPTION_INVALID_STATUS);
        $this->user->setStatus(self::STATUS_INVALID);
    }
    
    public function testGetStatus_invalidstatus() {
        try {
            $this->user->setStatus(self::STATUS_INVALID);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->user->getStatus());
    }
    
    public function testSetGetStatus() {
        $this->assertEquals(self::STATUS, $this->user->setStatus(self::STATUS));
        $this->assertEquals(self::STATUS, $this->user->getStatus());
    }

    public function testSetGetModerationStatus() {
        $this->assertEquals($this->moderationStatus, 
                $this->user->setModerationStatus($this->moderationStatus));
        $this->assertEquals($this->moderationStatus, 
                $this->user->getModerationStatus());
    }
    
    public function testSetGetRegistrationDateTime() {
        $this->assertEquals($this->registrationDateTime, 
                $this->user->setRegistrationDateTime($this->registrationDateTime));
        $this->assertEquals($this->registrationDateTime, $this->user->getRegistrationDateTime());
    }


    public function testSetFirstTime_invalidfirsttime() {
        $this->setExpectedException('Exposure\Model\UserException', 
            User::EXCEPTION_INVALID_FIRST_TIME);
        $this->user->setFirstTime(self::FIRST_TIME_INVALID);
    }
    
    public function testGetFirstTime_invalidfirsttime() {
        try {
            $this->user->setFirstTime(self::FIRST_TIME_INVALID);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->user->getFirstTime());
    }
    
    public function testSetGetFirstTime() {
        $this->assertEquals(self::FIRST_TIME, $this->user->setFirstTime(self::FIRST_TIME));
        $this->assertEquals(self::FIRST_TIME, $this->user->getFirstTime());
    }
    
    public function testSetType_null() {
        $this->setExpectedException('Exposure\Model\UserException', 
            User::EXCEPTION_INVALID_TYPE);
        $this->user->setType(null);
    }
    
    public function testGetType_null() {
        try {
            $this->user->setType(null);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->user->getType());
    }
    
    public function testSetType_invalidtype() {
        $this->setExpectedException('Exposure\Model\UserException', 
            User::EXCEPTION_INVALID_TYPE);
        $this->user->setType(self::TYPE_INVALID);
    }
    
    public function testGetType_invalidtype() {
        try {
            $this->user->setType(self::TYPE_INVALID);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->user->getType());
    }
    
    public function testSetGetType() {
        $this->assertEquals(self::TYPE, $this->user->setType(self::TYPE));
        $this->assertEquals(self::TYPE, $this->user->getType());
    }

    public function testAddOwnedProject_duplicateownedproject() {
        $this->user->addOwnedProject($this->ownedProject);
        $this->setExpectedException('Exposure\Model\UserException', 
            User::EXCEPTION_DUPLICATE_OWNED_PROJECT);
        $this->user->addOwnedProject($this->ownedProject);
    }

    public function testAddOwnedProject() {
        $this->user->addOwnedProject($this->ownedProject);
        $this->assertEquals(1, $this->user->getOwnedProjects()->count());
    }
    
    public function testRemoveOwnedProject() {
        $this->assertEquals(0, $this->user->getOwnedProjects()->count());
        $this->user->addOwnedProject($this->ownedProject);
        $this->assertEquals(1, $this->user->getOwnedProjects()->count());
        $dummyOwnedProject = new Project;
        $this->assertFalse($this->user->removeOwnedProject($dummyOwnedProject));
        $this->assertEquals(1, $this->user->getOwnedProjects()->count());
        $this->assertTrue($this->user->removeOwnedProject($this->ownedProject));
        $this->assertEquals(0, $this->user->getOwnedProjects()->count());
    }
    
    public function testAddTempDraftProjectPhoto_duplicatetempdraftprojectphoto() {
        $this->user->addTempDraftProjectPhoto($this->tempDraftProjectPhoto);
        $this->setExpectedException('Exposure\Model\UserException', 
            User::EXCEPTION_DUPLICATE_TEMP_DRAFT_PROJECT_PHOTO);
        $this->user->addTempDraftProjectPhoto($this->tempDraftProjectPhoto);
    }

    public function testAddTempDraftProjectPhoto() {
        $this->user->addTempDraftProjectPhoto($this->tempDraftProjectPhoto);
        $this->assertEquals(1, $this->user->getTempDraftProjectPhotos()->count());
    }
    
    public function testRemoveTempDraftProjectPhoto() {
        $this->assertEquals(0, $this->user->getTempDraftProjectPhotos()->count());
        $this->user->addTempDraftProjectPhoto($this->tempDraftProjectPhoto);
        $this->assertEquals(1, $this->user->getTempDraftProjectPhotos()->count());
        $dummyTempDraftProjectPhoto = new LabelledImage;
        $this->assertFalse($this->user->removeTempDraftProjectPhoto($dummyTempDraftProjectPhoto));
        $this->assertEquals(1, $this->user->getTempDraftProjectPhotos()->count());
        $this->assertTrue($this->user->removeTempDraftProjectPhoto($this->tempDraftProjectPhoto));
        $this->assertEquals(0, $this->user->getTempDraftProjectPhotos()->count());
    }
  
    public function testAddNotification_invalid() {
        $notification = $this->getMockForAbstractClass('Exposure\Model\Notification');
        $this->setExpectedException('Exposure\Model\UserException', 
                User::EXCEPTION_INVALID_NOTIFICATION_TYPE);
        $this->user->addNotification($notification);
    }
    
    public function testAddNotification() {
        $this->user->addNotification($this->notification);
        $this->assertEquals(1, $this->user->getNotifications()->count());
    }
    
    public function testRemoveNotification() {
        $this->assertEquals(0, $this->user->getNotifications()->count());
        $this->user->addNotification($this->notification);
        $this->assertEquals(1, $this->user->getNotifications()->count());
        $dummyNotification = new ProjectThemeSuggestionNotification();
        $this->assertFalse($this->user->removeNotification($dummyNotification));
        $this->assertEquals(1, $this->user->getNotifications()->count());
        $this->assertTrue($this->user->removeNotification($this->notification));
        $this->assertEquals(0, $this->user->getNotifications()->count());
    }
  
    public function testSetGetPhoto() {
        $this->assertEquals($this->photo, $this->user->setPhoto($this->photo));
        $this->assertEquals($this->photo, $this->user->getPhoto());
    }
    
    public function testSetGetPlace() {
        $this->assertEquals($this->location, $this->user->setPlace($this->location));
        $this->assertEquals($this->location, $this->user->getPlace());
        $this->assertEquals($this->country, $this->user->setPlace($this->country));
        $this->assertEquals($this->country, $this->user->getPlace());
    }
    
    public function testSetGetBillingAddress() {
        $this->assertNull($this->user->setBillingAddress(null));
        $this->assertNull($this->user->getBillingAddress());
        $this->assertEquals($this->billingAddress, 
                $this->user->setBillingAddress($this->billingAddress));
        $this->assertEquals($this->billingAddress, 
                $this->user->getBillingAddress());
    }
    
    public function testSetPresentation_empty() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_EMPTY);
        $this->user->setPresentation($this->presentation_empty);
    }
    
    public function testGetPresentation_empty() {
        try {
            $this->user->setPresentation($this->presentation_empty);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->user->getPresentation());
    }
    
    public function testSetPresentation_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_TOO_LONG);
        $this->user->setPresentation($this->presentation_toolong);
    }
    
    public function testGetPresentation_toolong() {
        try {
            $this->user->setPresentation($this->presentation_toolong);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->user->getPresentation());
    }
    
    public function testSetGetPresentation() {
        $this->assertEquals($this->presentation, 
                $this->user->setPresentation($this->presentation));
        $this->assertEquals($this->presentation, $this->user->getPresentation());
    }
    
    public function testSetGetNextSubscription() {
        $this->assertNull($this->user->setNextSubscription(null));
        $this->assertNull($this->user->getNextSubscription());
        $this->assertEquals($this->nextSubscription, 
                $this->user->setNextSubscription($this->nextSubscription));
        $this->assertEquals($this->nextSubscription, 
                $this->user->getNextSubscription());
    }
    
    public function testSetGetCurrentSubscription() {
        $this->assertNull($this->user->setCurrentSubscription(null));
        $this->assertNull($this->user->getCurrentSubscription());
        $this->assertEquals($this->currentSubscription, 
                $this->user->setCurrentSubscription($this->currentSubscription));
        $this->assertEquals($this->currentSubscription, 
                $this->user->getCurrentSubscription());
    }
    
    public function testAddPastSubscription() {
        $this->user->addPastSubscription($this->pastSubscription);
        $this->assertEquals(1, $this->user->getPastSubscriptions()->count());
    }
    
    public function testRemovePastSubscription() {
        $this->assertEquals(0, $this->user->getPastSubscriptions()->count());
        $this->user->addPastSubscription($this->pastSubscription);
        $this->assertEquals(1, $this->user->getPastSubscriptions()->count());
        $dummyPastSubscription = new Subscription();
        $this->assertFalse($this->user->removePastSubscription($dummyPastSubscription));
        $this->assertEquals(1, $this->user->getPastSubscriptions()->count());
        $this->assertTrue($this->user->removePastSubscription($this->pastSubscription));
        $this->assertEquals(0, $this->user->getPastSubscriptions()->count());
    }

    public function testSetLanguageCode_notastring() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_NOT_A_STRING);
        $this->user->setLanguageCode(array());
    }
    
    public function testGetLanguageCode_notastring() {
        try {
            $this->user->setLanguageCode(array());
        }
        catch (\Exception $e) {}
        $this->assertNull($this->user->getLanguageCode());
    }
    
    public function testSetLanguageCode_empty() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_EMPTY);
        $this->user->setLanguageCode('');
    }
    
    public function testGetLanguageCode_empty() {
        try {
            $this->user->setLanguageCode('');
        }
        catch (\Exception $e) {}
        $this->assertNull($this->user->getLanguageCode());
    }
    
    public function testSetLanguageCode_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_TOO_LONG);
        $this->user->setLanguageCode(str_repeat('a', Language::CODE_MAX_LENGTH + 1));
    }
    
    public function testGetLanguageCode_toolong() {
        try {
            $this->user->setLanguageCode(str_repeat('a', Language::CODE_MAX_LENGTH + 1));
        }
        catch (\Exception $e) {}
        $this->assertNull($this->user->getLanguageCode());
    }

    public function testSetGetLanguageCode() {
        $this->assertNull($this->user->setLanguageCode(null));
        $this->assertNull($this->user->getLanguageCode());
        $this->assertEquals(self::LANGUAGE_CODE, $this->user->setLanguageCode(self::LANGUAGE_CODE));
        $this->assertEquals(self::LANGUAGE_CODE, $this->user->getLanguageCode());
    }
    
    public function testSetCurrencyCode_notastring() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_NOT_A_STRING);
        $this->user->setCurrencyCode(array());
    }
    
    public function testGetCurrencyCode_notastring() {
        try {
            $this->user->setCurrencyCode(array());
        }
        catch (\Exception $e) {}
        $this->assertNull($this->user->getCurrencyCode());
    }
    
    public function testSetCurrencyCode_incorrectlength() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_INCORRECT_LENGTH);
        $this->user->setCurrencyCode(str_repeat('a', Currency::CODE_LENGTH + 1));
    }
    
    public function testGetCurrencyCode_incorrectlength() {
        try {
            $this->user->setCurrencyCode(str_repeat('a', Currency::CODE_LENGTH + 1));
        }
        catch (\Exception $e) {}
        $this->assertNull($this->user->getCurrencyCode());
    }

    public function testSetGetCurrencyCode() {
        $this->assertNull($this->user->setCurrencyCode(null));
        $this->assertNull($this->user->getCurrencyCode());
        $this->assertEquals(self::CURRENCY_CODE, $this->user->setCurrencyCode(self::CURRENCY_CODE));
        $this->assertEquals(self::CURRENCY_CODE, $this->user->getCurrencyCode());
    }
    
    public function testAddBookmarkedProject() {
        $this->user->addBookmarkedProject($this->bookmarkedProject);
        $this->assertEquals(1, $this->user->getBookmarkedProjects()->count());
    }
    
    public function testRemoveBookmarkedProject() {
        $this->assertEquals(0, $this->user->getBookmarkedProjects()->count());
        $this->user->addBookmarkedProject($this->bookmarkedProject);
        $this->assertEquals(1, $this->user->getBookmarkedProjects()->count());
        $dummyBookmarkedProject = new Project();
        $this->assertFalse($this->user->removeBookmarkedProject($dummyBookmarkedProject));
        $this->assertEquals(1, $this->user->getBookmarkedProjects()->count());
        $this->assertTrue($this->user->removeBookmarkedProject($this->bookmarkedProject));
        $this->assertEquals(0, $this->user->getBookmarkedProjects()->count());
    }
    
    public function testAddBookmarkedProject_duplicatebookmarkedproject() {
        $this->user->addBookmarkedProject($this->bookmarkedProject);
        $this->setExpectedException('Exposure\Model\UserException', 
            User::EXCEPTION_DUPLICATE_BOOKMARKED_PROJECT);
        $this->user->addBookmarkedProject($this->bookmarkedProject);
    }
    
    public function testSetGetProjectOwnerPreferences() {
        $this->assertNull($this->user->setProjectOwnerPreferences());
        $this->assertNull($this->user->getProjectOwnerPreferences());
        $this->assertEquals($this->projectOwnerPreferences, 
                $this->user->setProjectOwnerPreferences($this->projectOwnerPreferences));
        $this->assertEquals($this->projectOwnerPreferences, 
                $this->user->getProjectOwnerPreferences());
    }

    public function testSetGetSponsorUserPreferences() {
        $this->assertNull($this->user->setSponsorUserPreferences());
        $this->assertNull($this->user->getSponsorUserPreferences());
        $this->assertEquals($this->sponsorUserPreferences, 
                $this->user->setSponsorUserPreferences($this->sponsorUserPreferences));
        $this->assertEquals($this->sponsorUserPreferences, 
                $this->user->getSponsorUserPreferences());
    }
    
    public function testValidatePartial_missingemail() {
        $this->user->setAuthenticator($this->authenticator);
        $this->user->setStatus(self::STATUS);
        $this->user->setModerationStatus($this->moderationStatus);
        $this->user->setRegistrationDateTime($this->registrationDateTime);
        $this->user->setType(self::TYPE);
        $this->setExpectedException('Sociable\Utility\StringException', 
            StringValidator::EXCEPTION_NOT_A_STRING);
        $this->user->validatePartial();
    }
    
    public function testValidatePartial_missingauthenticator() {
        $this->user->setEmail(self::EMAIL);
        $this->user->setStatus(self::STATUS);
        $this->user->setModerationStatus($this->moderationStatus);
        $this->user->setRegistrationDateTime($this->registrationDateTime);
        $this->user->setType(self::TYPE);
        $this->setExpectedException('Sociable\Model\UserException', 
            User::EXCEPTION_MISSING_AUTHENTICATOR);
        $this->user->validatePartial();
    }
    
    public function testValidatePartial_missingstatus() {
        $this->user->setEmail(self::EMAIL);
        $this->user->setAuthenticator($this->authenticator);
        $this->user->setModerationStatus($this->moderationStatus);
        $this->user->setRegistrationDateTime($this->registrationDateTime);
        $this->user->setType(self::TYPE);
        $this->setExpectedException('Exposure\Model\UserException', 
            User::EXCEPTION_INVALID_STATUS);
        $this->user->validatePartial();
    }
    
    public function testValidatePartial_missingmoderationstatus() {
        $this->user->setEmail(self::EMAIL);
        $this->user->setAuthenticator($this->authenticator);
        $this->user->setStatus(self::STATUS);
        $this->user->setRegistrationDateTime($this->registrationDateTime);
        $this->user->setType(self::TYPE);
        $this->setExpectedException('Exposure\Model\UserException', 
            User::EXCEPTION_INVALID_MODERATION_STATUS);
        $this->user->validatePartial();
    }
    
    public function testValidatePartial_missingregistrationdatetime() {
        $this->user->setEmail(self::EMAIL);
        $this->user->setAuthenticator($this->authenticator);
        $this->user->setStatus(self::STATUS);
        $this->user->setModerationStatus($this->moderationStatus);
        $this->user->setType(self::TYPE);
        $this->setExpectedException('Exposure\Model\UserException', 
            User::EXCEPTION_INVALID_REGISTRATION_DATE_TIME);
        $this->user->validatePartial();
    }
    
    public function testValidatePartial_missingtype() {
        $this->user->setEmail(self::EMAIL);
        $this->user->setAuthenticator($this->authenticator);
        $this->user->setStatus(self::STATUS);
        $this->user->setModerationStatus($this->moderationStatus);
        $this->user->setRegistrationDateTime($this->registrationDateTime);
        $this->setExpectedException('Exposure\Model\UserException', 
            User::EXCEPTION_INVALID_TYPE);
        $this->user->validatePartial();
    }
    
    public function testValidatePartial() {
        $this->user->setEmail(self::EMAIL);
        $this->user->setAuthenticator($this->authenticator);
        $this->user->setStatus(self::STATUS);
        $this->user->setModerationStatus($this->moderationStatus);
        $this->user->setRegistrationDateTime($this->registrationDateTime);
        $this->user->setType(self::TYPE);
        $this->user->validatePartial();
    }
    
    public function testValidate_missingname() {
        $this->user->setSurname(self::SURNAME);
        $this->user->setEmail(self::EMAIL);
        $this->user->setAuthenticator($this->authenticator);
        $this->user->setStatus(self::STATUS);
        $this->user->setModerationStatus($this->moderationStatus);
        $this->user->setRegistrationDateTime($this->registrationDateTime);
        $this->user->setType(self::TYPE);
        $this->user->setPhoto($this->photo);
        $this->user->setPlace($this->location);
        $this->user->setPresentation($this->presentation);
        $this->setExpectedException('Sociable\Utility\StringException', 
            StringValidator::EXCEPTION_NOT_A_STRING);
        $this->user->validate();
    }
    
    public function testValidate_missingsurname() {
        $this->user->setName(self::NAME);
        $this->user->setEmail(self::EMAIL);
        $this->user->setAuthenticator($this->authenticator);
        $this->user->setStatus(self::STATUS);
        $this->user->setModerationStatus($this->moderationStatus);
        $this->user->setRegistrationDateTime($this->registrationDateTime);
        $this->user->setType(self::TYPE);
        $this->user->setPhoto($this->photo);
        $this->user->setPlace($this->location);
        $this->user->setPresentation($this->presentation);
        $this->setExpectedException('Sociable\Utility\StringException', 
            StringValidator::EXCEPTION_NOT_A_STRING);
        $this->user->validate();
    }
    
    public function testValidate_missingemail() {
        $this->user->setName(self::NAME);
        $this->user->setSurname(self::SURNAME);
        $this->user->setAuthenticator($this->authenticator);
        $this->user->setStatus(self::STATUS);
        $this->user->setModerationStatus($this->moderationStatus);
        $this->user->setRegistrationDateTime($this->registrationDateTime);
        $this->user->setType(self::TYPE);
        $this->user->setPhoto($this->photo);
        $this->user->setPlace($this->location);
        $this->user->setPresentation($this->presentation);
        $this->setExpectedException('Sociable\Utility\StringException', 
            StringValidator::EXCEPTION_NOT_A_STRING);
        $this->user->validate();
    }
    
    public function testValidate_missingauthenticator() {
        $this->user->setName(self::NAME);
        $this->user->setSurname(self::SURNAME);
        $this->user->setEmail(self::EMAIL);
        $this->user->setStatus(self::STATUS);
        $this->user->setModerationStatus($this->moderationStatus);
        $this->user->setRegistrationDateTime($this->registrationDateTime);
        $this->user->setType(self::TYPE);
        $this->user->setPhoto($this->photo);
        $this->user->setPlace($this->location);
        $this->user->setPresentation($this->presentation);
        $this->setExpectedException('Sociable\Model\UserException', 
            User::EXCEPTION_MISSING_AUTHENTICATOR);
        $this->user->validate();
    }
    
    public function testValidate_missingstatus() {
        $this->user->setName(self::NAME);
        $this->user->setSurname(self::SURNAME);
        $this->user->setEmail(self::EMAIL);
        $this->user->setAuthenticator($this->authenticator);
        $this->user->setModerationStatus($this->moderationStatus);
        $this->user->setRegistrationDateTime($this->registrationDateTime);
        $this->user->setType(self::TYPE);
        $this->user->setPhoto($this->photo);
        $this->user->setPlace($this->location);
        $this->user->setPresentation($this->presentation);
        $this->setExpectedException('Exposure\Model\UserException', 
            User::EXCEPTION_INVALID_STATUS);
        $this->user->validate();
    }
    
    public function testValidate_missingmoderationstatus() {
        $this->user->setName(self::NAME);
        $this->user->setSurname(self::SURNAME);
        $this->user->setEmail(self::EMAIL);
        $this->user->setAuthenticator($this->authenticator);
        $this->user->setStatus(self::STATUS);
        $this->user->setRegistrationDateTime($this->registrationDateTime);
        $this->user->setType(self::TYPE);
        $this->user->setPhoto($this->photo);
        $this->user->setPlace($this->location);
        $this->user->setPresentation($this->presentation);
        $this->setExpectedException('Exposure\Model\UserException', 
            User::EXCEPTION_INVALID_MODERATION_STATUS);
        $this->user->validate();
    }
    
    public function testValidate_missingregistrationdatetime() {
        $this->user->setName(self::NAME);
        $this->user->setSurname(self::SURNAME);
        $this->user->setEmail(self::EMAIL);
        $this->user->setAuthenticator($this->authenticator);
        $this->user->setStatus(self::STATUS);
        $this->user->setModerationStatus($this->moderationStatus);
        $this->user->setType(self::TYPE);
        $this->user->setPhoto($this->photo);
        $this->user->setPlace($this->location);
        $this->user->setPresentation($this->presentation);
        $this->setExpectedException('Exposure\Model\UserException', 
            User::EXCEPTION_INVALID_REGISTRATION_DATE_TIME);
        $this->user->validate();
    }
    
    public function testValidate_missingtype() {
        $this->user->setName(self::NAME);
        $this->user->setSurname(self::SURNAME);
        $this->user->setEmail(self::EMAIL);
        $this->user->setAuthenticator($this->authenticator);
        $this->user->setStatus(self::STATUS);
        $this->user->setModerationStatus($this->moderationStatus);
        $this->user->setRegistrationDateTime($this->registrationDateTime);
        $this->user->setPhoto($this->photo);
        $this->user->setPlace($this->location);
        $this->user->setPresentation($this->presentation);
        $this->setExpectedException('Exposure\Model\UserException', 
            User::EXCEPTION_INVALID_TYPE);
        $this->user->validate();
    }
    
    public function testValidate_missingphoto() {
        $this->user->setName(self::NAME);
        $this->user->setSurname(self::SURNAME);
        $this->user->setEmail(self::EMAIL);
        $this->user->setAuthenticator($this->authenticator);
        $this->user->setStatus(self::STATUS);
        $this->user->setModerationStatus($this->moderationStatus);
        $this->user->setRegistrationDateTime($this->registrationDateTime);
        $this->user->setType(self::TYPE);
        $this->user->setPlace($this->location);
        $this->user->setPresentation($this->presentation);
        $this->setExpectedException('Exposure\Model\UserException', 
            User::EXCEPTION_INVALID_PHOTO);
        $this->user->validate();
    }
    
    public function testValidate_missinglocation() {
        $this->user->setName(self::NAME);
        $this->user->setSurname(self::SURNAME);
        $this->user->setEmail(self::EMAIL);
        $this->user->setAuthenticator($this->authenticator);
        $this->user->setStatus(self::STATUS);
        $this->user->setModerationStatus($this->moderationStatus);
        $this->user->setRegistrationDateTime($this->registrationDateTime);
        $this->user->setType(self::TYPE);
        $this->user->setPhoto($this->photo);
        $this->user->setPresentation($this->presentation);
        $this->setExpectedException('Exposure\Model\UserException', 
            User::EXCEPTION_INVALID_PLACE);
        $this->user->validate();
    }
    
    public function testValidate_missingpresentation() {
        $this->user->setName(self::NAME);
        $this->user->setSurname(self::SURNAME);
        $this->user->setEmail(self::EMAIL);
        $this->user->setAuthenticator($this->authenticator);
        $this->user->setStatus(self::STATUS);
        $this->user->setModerationStatus($this->moderationStatus);
        $this->user->setRegistrationDateTime($this->registrationDateTime);
        $this->user->setType(self::TYPE);
        $this->user->setPhoto($this->photo);
        $this->user->setPlace($this->location);
        $this->setExpectedException('Exposure\Model\UserException', 
            User::EXCEPTION_INVALID_PRESENTATION);
        $this->user->validate();
    }
    
    public function testValidate() {
        $this->user->setName(self::NAME);
        $this->user->setSurname(self::SURNAME);
        $this->user->setEmail(self::EMAIL);
        $this->user->setAuthenticator($this->authenticator);
        $this->user->setStatus(self::STATUS);
        $this->user->setModerationStatus($this->moderationStatus);
        $this->user->setRegistrationDateTime($this->registrationDateTime);
        $this->user->setType(self::TYPE);
        $this->user->setPhoto($this->photo);
        $this->user->setPlace($this->location);
        $this->user->setPresentation($this->presentation);
        $this->user->validate();
        
        $this->user->setEmailConfirmationCode($this->emailConfirmationCode);
        $this->user->setPasswordResetCode($this->passwordResetCode);
        $this->user->setFirstTime(self::FIRST_TIME);
        $this->user->addOwnedProject($this->ownedProject);
        $this->user->setBillingAddress($this->billingAddress);
        $this->user->setNextSubscription($this->nextSubscription);
        $this->user->setCurrentSubscription($this->currentSubscription);
        $this->user->addPastSubscription($this->pastSubscription);
        $this->user->setLanguageCode(self::LANGUAGE_CODE);
        $this->user->setCurrencyCode(self::CURRENCY_CODE);
        $this->user->addBookmarkedProject($this->bookmarkedProject);
        $this->user->setProjectOwnerPreferences($this->projectOwnerPreferences);
        $this->user->setSponsorUserPreferences($this->sponsorUserPreferences);
        $this->user->validate();
    }

    public function tearDown() {
        unset($this->user);
    }

}

?>
