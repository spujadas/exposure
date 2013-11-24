<?php

/*
 * This file is part of the Exposure package.
 *
 * Copyright 2013 by Sébastien Pujadas
 *
 * For the full copyright and licence information, please view the LICENCE
 * file that was distributed with this source code.
 */

namespace Exposure\Tests\ODM;

use Exposure\Model\User,
    Sociable\Model\PasswordAuthenticator,
    Sociable\Model\ConfirmationCode,
    Exposure\Model\ModerationStatus,
    Exposure\Model\ProfileModerationNotification,
    Exposure\Model\Project,
    Sociable\Model\LabelledImage,
    Sociable\Model\Location,
    Sociable\Model\Country,
    Sociable\Model\Address,
    Exposure\Model\Subscription,
    Exposure\Model\ProjectOwnerPreferences,
    Exposure\Model\SponsorUserPreferences,
    Sociable\Model\MultiLanguageString,
    Sociable\ODM\ObjectDocumentMapper;

class UserODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $user;
    protected static $id = null;
    
    const EMAIL = 'zzzzzz@zzzzzz.com';
    const NAME = 'Foo';
    const SURNAME = 'Bar';
    protected $authenticator;
    protected $emailConfirmationCode;
    protected $passwordResetCode;
    const STATUS = User::STATUS_REGISTERED;
    protected $moderationStatus;
    protected $registrationDateTime;
    const TYPE = User::TYPE_PROJECT_OWNER;
    
    protected $ownedProject;
    const OWNED_PROJECT_NAME = 'ZZZZZZ_owned_project_name';
    
    protected $notification;
    protected static $notificationId = null;
    
    const PHOTO_FILE_NAME = 'photo.png';
    protected $photo;
    protected static $photoId = null;

    const TEMP_DRAFT_PROJECT_PHOTO_FILE_NAME = 'photo.png';
    protected $tempDraftProjectPhoto;
    protected static $tempDraftProjectPhotoId = null;

    protected $location;
    const LOCATION_LABEL = 'ZZZZZZ_location_label';

    protected $country;
    const COUNTRY_CODE = 'ZZ';

    protected $billingAddress;
    protected $presentation;
    protected $nextSubscription;
    protected static $nextSubscriptionId = null;
        
    protected $currentSubscription;
    protected static $currentSubscriptionId = null;
    
    protected $pastSubscription;
    protected static $pastSubscriptionId = null;
    
    const LANGUAGE_CODE = 'fr';
    const CURRENCY_CODE = 'EUR';
    
    protected $bookmarkedProject;
    const BOOKMARKED_PROJECT_NAME = 'ZZZZZZ_bookmarked_project_name';
    
    protected $projectOwnerPreferences;
    protected $sponsorUserPreferences;

    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }

    public function setUp() {
        self::cleanup();
        
        $this->user = new User;
        $this->user->setEmail(self::EMAIL);
        $this->user->setName(self::NAME);
        $this->user->setSurname(self::SURNAME);
        
        $this->authenticator = new PasswordAuthenticator();
        $this->authenticator->setParams(array('password' => '1234'));
        $this->user->setAuthenticator($this->authenticator);
        
        $this->emailConfirmationCode = new ConfirmationCode();
        $this->user->setEmailConfirmationCode($this->emailConfirmationCode);
        
        $this->passwordResetCode = new ConfirmationCode();
        $this->user->setPasswordResetCode($this->passwordResetCode);
        
        $this->user->setStatus(self::STATUS);
        
        $this->moderationStatus = new ModerationStatus();
        $this->moderationStatus->setStatus(ModerationStatus::STATUS_APPROVED);
        $this->moderationStatus->setReasonCode('s');
        $this->moderationStatus->setComment('comment');
        $this->user->setModerationStatus($this->moderationStatus);
        
        $this->registrationDateTime = new \DateTime;
        $this->user->setRegistrationDateTime($this->registrationDateTime);
        
        $this->user->setType(self::TYPE);
        
        $this->ownedProject = new Project;
        $this->ownedProject->setName(self::OWNED_PROJECT_NAME);
        $this->user->addOwnedProject($this->ownedProject);
        
        $this->notification = new ProfileModerationNotification;
        $this->user->addNotification($this->notification);
        
        $this->photo = new LabelledImage();
        $this->photo->setImageFile(__DIR__ . '/' . self::PHOTO_FILE_NAME);
        $this->photo->setMime(LabelledImage::MIME_PNG);
        $this->photo->setDescription(new MultiLanguageString('photo utilisateur', 'fr'));
        $this->user->setPhoto($this->photo);
         
        $this->tempDraftProjectPhoto = new LabelledImage();
        $this->tempDraftProjectPhoto->setImageFile(__DIR__ . '/' . self::PHOTO_FILE_NAME);
        $this->tempDraftProjectPhoto->setMime(LabelledImage::MIME_PNG);
        $this->tempDraftProjectPhoto->setDescription(new MultiLanguageString('photo projet temporaire', 'fr'));
        $this->user->addTempDraftProjectPhoto($this->tempDraftProjectPhoto);
        
        $this->location = new Location();
        $this->location->setLabel(self::LOCATION_LABEL);
        $this->user->setPlace($this->location);
        
        $this->country = new Country;
        $this->country->setCode(self::COUNTRY_CODE);

        $this->billingAddress = new Address;
        $this->billingAddress->setAddress1('1 rue du Château');
        $this->billingAddress->setPostCode('92290');
        $this->billingAddress->setCityOrTownOrVillage('Châtenay-Malabry');
        $this->billingAddress->setCountry($this->country);
        $this->user->setBillingAddress($this->billingAddress);
        
        $this->presentation = new MultiLanguageString('foo', 'fr');
        $this->user->setPresentation($this->presentation);
        
        $this->nextSubscription = new Subscription;
        $this->user->setNextSubscription($this->nextSubscription);
        
        $this->currentSubscription = new Subscription;
        $this->user->setCurrentSubscription($this->currentSubscription);
        
        $this->pastSubscription = new Subscription;
        $this->user->addPastSubscription($this->pastSubscription);
        
        $this->user->setCurrencyCode(self::CURRENCY_CODE);
        $this->user->setLanguageCode(self::LANGUAGE_CODE);
        
        $this->bookmarkedProject = new Project;
        $this->bookmarkedProject->setName(self::BOOKMARKED_PROJECT_NAME);
        $this->user->addBookmarkedProject($this->bookmarkedProject);
        
        $this->projectOwnerPreferences = new ProjectOwnerPreferences;
        $this->user->setProjectOwnerPreferences($this->projectOwnerPreferences);
        
        $this->sponsorUserPreferences = new SponsorUserPreferences;
        $this->user->setSponsorUserPreferences($this->sponsorUserPreferences);

        $this->user->validate();
        
        self::$dm->persist($this->country);
        self::$dm->persist($this->user);
        self::$dm->persist($this->ownedProject);
        self::$dm->persist($this->notification);
        self::$dm->persist($this->photo);
        self::$dm->persist($this->tempDraftProjectPhoto);
        self::$dm->persist($this->location);
        self::$dm->persist($this->nextSubscription);
        self::$dm->persist($this->currentSubscription);
        self::$dm->persist($this->pastSubscription);
        self::$dm->persist($this->bookmarkedProject);
        
        self::$dm->flush();
        
        self::$id = $this->user->getId();
        self::$currentSubscriptionId = $this->currentSubscription->getId();
        self::$nextSubscriptionId = $this->nextSubscription->getId();
        self::$pastSubscriptionId = $this->pastSubscription->getId();
        self::$notificationId = $this->notification->getId();
        self::$photoId = $this->photo->getId();
        self::$tempDraftProjectPhotoId = $this->tempDraftProjectPhoto->getId();
        
        self::$dm->clear();
    }

    public function testFound() {
        $this->user = ObjectDocumentMapper::getByEmail(self::$dm, 
            'Exposure\Model\User', self::EMAIL);
        $this->assertNotNull($this->user);
    }
    
    public function testIsValid() {
        $this->user = ObjectDocumentMapper::getByEmail(self::$dm, 
            'Exposure\Model\User', self::EMAIL);
        $this->user->validate();
    }
   
    public function testIsEqual() {
        $this->user = ObjectDocumentMapper::getByEmail(self::$dm, 
            'Exposure\Model\User', self::EMAIL);
        
        $this->assertEquals(self::EMAIL, $this->user->getEmail());
        $this->assertEquals(self::NAME, $this->user->getName());
        $this->assertEquals(self::SURNAME, $this->user->getSurname());
        $this->assertEquals($this->authenticator, $this->user->getAuthenticator());
        $this->assertEquals($this->emailConfirmationCode, $this->user->getEmailConfirmationCode());
        $this->assertEquals($this->passwordResetCode, $this->user->getPasswordResetCode());
        $this->assertEquals(self::STATUS, $this->user->getStatus());
        $this->assertEquals($this->moderationStatus, $this->user->getModerationStatus());
        $this->assertEquals($this->registrationDateTime, $this->user->getRegistrationDateTime());
        $this->assertEquals(self::TYPE, $this->user->getType());
        $this->assertEquals(self::OWNED_PROJECT_NAME, $this->user->getOwnedProjects()[0]->getName());
        $this->assertEquals(self::$notificationId, $this->user->getNotifications()[0]->getId());
        $this->assertEquals(self::$photoId, $this->user->getPhoto()->getId());
        $this->assertEquals(sha1_file(__DIR__ . '/' . self::PHOTO_FILE_NAME), 
            sha1($this->user->getPhoto()->getImageFile()->getBytes()));
        $this->assertEquals(sha1_file(__DIR__ . '/' . self::TEMP_DRAFT_PROJECT_PHOTO_FILE_NAME), 
            sha1($this->user->getTempDraftProjectPhotos()->first()->getImageFile()->getBytes()));
        $this->assertEquals(self::LOCATION_LABEL, $this->user->getPlace()->getLabel());
        $billingAddress = $this->user->getBillingAddress();
        $this->assertEquals($this->billingAddress->getAddress1(), 
            $billingAddress->getAddress1());
        $this->assertEquals($this->billingAddress->getPostCode(), 
            $billingAddress->getPostCode());
        $this->assertEquals($this->billingAddress->getCityOrTownOrVillage(), 
            $billingAddress->getCityOrTownOrVillage());
        $this->assertEquals($this->billingAddress->getCountry()->getCode(), 
            $billingAddress->getCountry()->getCode());
        $this->assertEquals($this->presentation, $this->user->getPresentation());
        $this->assertEquals(self::$nextSubscriptionId, $this->user->getNextSubscription()->getId());
        $this->assertEquals(self::$currentSubscriptionId, $this->user->getCurrentSubscription()->getId());
        $this->assertEquals(self::$pastSubscriptionId, $this->user->getPastSubscriptions()[0]->getId());
        $this->assertEquals(self::CURRENCY_CODE, $this->user->getCurrencyCode());
        $this->assertEquals(self::LANGUAGE_CODE, $this->user->getLanguageCode());
        $this->assertEquals(self::BOOKMARKED_PROJECT_NAME, $this->user->getBookmarkedProjects()[0]->getName());
        $this->assertEquals($this->projectOwnerPreferences, $this->user->getProjectOwnerPreferences());
        $this->assertEquals($this->sponsorUserPreferences, $this->user->getSponsorUserPreferences());
        $this->assertEquals(self::$id, $this->user->getOwnedProjects()[0]->getOwners()[0]->getId());
    }
   
    public function testRemove() {
        $this->user = ObjectDocumentMapper::getByEmail(self::$dm, 
            'Exposure\Model\User', self::EMAIL);
        self::$dm->remove($this->user);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getByEmail(self::$dm, 
            'Exposure\Model\User', self::EMAIL));
    }
    
    public function testDuplicate() {
        $this->user = new User();
        $this->user->setEmail(self::EMAIL);
        self::$dm->persist($this->user);
        $this->setExpectedException('MongoCursorException');
        self::$dm->flush();
    }
    
    public function tearDown() {
        self::cleanup();
    }
    
    public static function tearDownAfterClass() {
        self::cleanup();
    }
    
    public static function cleanUp() {
        self::$dm->clear();
        
        $user = ObjectDocumentMapper::getByEmail(self::$dm, 'Exposure\Model\User', self::EMAIL);
        if(!is_null($user)) {
            self::$dm->remove($user);
        }
        
        $ownedProject = ObjectDocumentMapper::getByName(self::$dm, 'Exposure\Model\Project', self::OWNED_PROJECT_NAME);
        if(!is_null($ownedProject)) {
            self::$dm->remove($ownedProject);
        }
        
        $country = ObjectDocumentMapper::getByCode(self::$dm, 'Sociable\Model\Country', self::COUNTRY_CODE);
        if(!is_null($country)) {
            self::$dm->remove($country);
        }
        
        if (!is_null(self::$notificationId)) {
            $notification = ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\ProfileModerationNotification', self::$notificationId);
            if(!is_null($notification)) {
                self::$dm->remove($notification);
            }
        }
        
        if (!is_null(self::$photoId)) {
            $photo = ObjectDocumentMapper::getById(self::$dm, 'Sociable\Model\LabelledImage', self::$photoId);
            if(!is_null($photo)) {
                self::$dm->remove($photo);
            }
        }
        
        if (!is_null(self::$tempDraftProjectPhotoId)) {
            $tempDraftProjectPhoto = ObjectDocumentMapper::getById(self::$dm, 'Sociable\Model\LabelledImage', self::$tempDraftProjectPhotoId);
            if(!is_null($tempDraftProjectPhoto)) {
                self::$dm->remove($tempDraftProjectPhoto);
            }
        }
        
        $location = ObjectDocumentMapper::getByLabel(self::$dm, 'Sociable\Model\Location', self::LOCATION_LABEL);
        if(!is_null($location)) {
            self::$dm->remove($location);
        }
        
        if (!is_null(self::$nextSubscriptionId)) {
            $nextSubscription = ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\Subscription', self::$nextSubscriptionId);
            if(!is_null($nextSubscription)) {
                self::$dm->remove($nextSubscription);
            }
        }
        
        if (!is_null(self::$currentSubscriptionId)) {
            $currentSubscription = ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\Subscription', self::$currentSubscriptionId);
            if(!is_null($currentSubscription)) {
                self::$dm->remove($currentSubscription);
            }
        }
        
        if (!is_null(self::$pastSubscriptionId)) {
            $pastSubscription = ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\Subscription', self::$pastSubscriptionId);
            if(!is_null($pastSubscription)) {
                self::$dm->remove($pastSubscription);
            }
        }
        
        $bookmarkedProject = ObjectDocumentMapper::getByName(self::$dm, 'Exposure\Model\Project', self::BOOKMARKED_PROJECT_NAME);
        if(!is_null($bookmarkedProject)) {
            self::$dm->remove($bookmarkedProject);
        }
        
        self::$dm->flush();
    }

}

?>
