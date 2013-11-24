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

use Exposure\Model\SponsorOrganisation,
    Exposure\Model\Theme,
    Exposure\Model\SponsorContributionTypes,
    Exposure\Model\SponsorReturnType,
    Exposure\Model\User,
    Exposure\Model\SponsorContributionNotification,
    Sociable\Model\BusinessSector,
    Sociable\Model\ContactDetails,
    Sociable\Model\MultiLanguageString,
    Sociable\Model\Organisation,
    Sociable\Model\LabelledImage,
    Sociable\Model\WebPresence,
    Sociable\ODM\ObjectDocumentMapper;

class SponsorOrganisationODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $businessSector;
    protected $organisation;
    protected static $id = null;

    const NAME = 'Foo Bar Ltd';
    const TYPE = Organisation::BUSINESS_ORGANISATION;
    const BUSINESS_SECTOR_CODE = 'code';
    const URL_SLUG = 'foo-bar-ltd';
    
    protected $description;
    const DESCRIPTION_STRING = 'description';
    const DESCRIPTION_LANGUAGE = 'fr';
    
    protected $logo;
    const LOGO_FILE_NAME = 'logo.png';
    protected static $logoId = null;
    
    protected $contactDetails;
    protected $webPresence;
    
    protected $soughtTheme;
    const THEME_LABEL = 'ZZZZZ';
    const THEME_NAME_STRING = 'ZZZZZ';
    const THEME_NAME_LANGUAGE = 'fr';
    
    protected $soughtContributionTypes;
    protected $soughtSponsorReturnType;
    protected static $soughtSponsorReturnTypeId = null;
    
    protected $sponsorUser;
    const SPONSOR_USER_EMAIL = 'zzzzzz@zzzzzz.com';
    
    protected $notification;
    protected static $notificationId = null;
    
    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanUp();
        
        $this->businessSector = new BusinessSector;

        $this->organisation = new SponsorOrganisation;
        $this->organisation->setName(self::NAME);
        $this->organisation->generateUrlSlug();
        $this->organisation->setType(self::TYPE);
        $this->organisation->setBusinessSector($this->businessSector);
        
        $this->description = new MultiLanguageString(self::DESCRIPTION_STRING,
                self::DESCRIPTION_LANGUAGE);
        $this->organisation->setDescription($this->description);
        
        $this->logo = new LabelledImage();
        $this->logo->setImageFile(__DIR__ . '/' . self::LOGO_FILE_NAME);
        $this->logo->setMime(LabelledImage::MIME_PNG);
        $this->logo->setDescription(new MultiLanguageString('chaîne de caractères', 'fr'));
        $this->organisation->setLogo($this->logo);
        
        $this->contactDetails = new ContactDetails;
        $this->organisation->setContactDetails($this->contactDetails);
        
        $this->webPresence = new WebPresence;
        $this->organisation->addWebPresence($this->webPresence);
        
        $this->soughtTheme = new Theme;
        $this->soughtTheme->setLabel(self::THEME_LABEL);
        $this->themeName = new MultiLanguageString(self::THEME_NAME_STRING, 
                self::THEME_NAME_LANGUAGE);
        $this->soughtTheme->setName($this->themeName);
        $this->organisation->addSoughtTheme($this->soughtTheme);
        
        $this->soughtContributionTypes = new SponsorContributionTypes;
        $this->organisation->setSoughtContributionTypes($this->soughtContributionTypes);
        
        $this->soughtSponsorReturnType = new SponsorReturnType;
        $this->organisation->addSoughtSponsorReturnType($this->soughtSponsorReturnType);
        
        $this->sponsorUser = new User;
        $this->sponsorUser->setEmail(self::SPONSOR_USER_EMAIL);
        $this->sponsorUser->setType(User::TYPE_SPONSOR);
        $this->organisation->addSponsorUser($this->sponsorUser);
        
        $this->notification = new SponsorContributionNotification;
        $this->organisation->addNotification($this->notification);
        
        $this->organisation->setCreationDateTime(new \DateTime);

        $this->organisation->validate();

        self::$dm->persist($this->organisation);
        self::$dm->persist($this->logo);
        self::$dm->persist($this->soughtTheme);
        self::$dm->persist($this->soughtSponsorReturnType);
        self::$dm->persist($this->sponsorUser);
        self::$dm->persist($this->notification);
        self::$dm->flush();
        
        self::$id = $this->organisation->getId();
        self::$logoId = $this->organisation->getLogo()->getId();
        self::$soughtSponsorReturnTypeId = $this->soughtSponsorReturnType->getId();
        self::$notificationId = $this->notification->getId();
        
        self::$dm->clear();
    }

    public function testFound() {
        $this->organisation = ObjectDocumentMapper::getByName(self::$dm, 'Exposure\Model\SponsorOrganisation', self::NAME);
        $this->assertNotNull($this->organisation);
    }
    
    public function testIsValid() {
        $this->organisation = ObjectDocumentMapper::getByName(self::$dm, 'Exposure\Model\SponsorOrganisation', self::NAME);
        $this->organisation->validate();
    }
   
    public function testIsEqual() {
        $organisation = ObjectDocumentMapper::getByName(self::$dm, 'Exposure\Model\SponsorOrganisation', self::NAME);
        $this->assertEquals($this->organisation->getName(), $organisation->getName());
        $this->assertEquals(self::DESCRIPTION_STRING, 
                $organisation->getDescription()->getStringByLanguageCode(self::DESCRIPTION_LANGUAGE));
        $this->assertEquals(sha1_file(__DIR__ . '/' . self::LOGO_FILE_NAME), 
                sha1($organisation->getLogo()->getImageFile()->getBytes()));
        $this->assertEquals($this->contactDetails, $organisation->getContactDetails());
        $this->assertEquals($this->webPresence, $organisation->getWebPresences()[0]);
        $this->assertEquals(self::THEME_LABEL, $organisation->getSoughtThemes()[0]->getLabel());
        $this->assertEquals(self::THEME_NAME_STRING, $organisation->getSoughtThemes()[0]->getName()
                ->getStringByLanguageCode(self::THEME_NAME_LANGUAGE));
        $this->assertEquals($this->soughtContributionTypes, 
                $organisation->getSoughtContributionTypes());
        $this->assertEquals(self::$soughtSponsorReturnTypeId,
                $organisation->getSoughtSponsorReturnTypes()[0]->getId());
        $this->assertEquals(self::SPONSOR_USER_EMAIL, $organisation->getSponsorUsers()[0]->getEmail());
        $this->assertEquals(self::$notificationId, $organisation->getNotifications()[0]->getId());
        $this->assertEquals(self::$id, $organisation->getSponsorUsers()[0]->getSponsorOrganisations()[0]->getId());
    }
   
    public function testRemove() {
        $this->organisation = ObjectDocumentMapper::getByName(self::$dm, 'Exposure\Model\SponsorOrganisation', self::NAME);
        self::$dm->remove($this->organisation);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getByName(self::$dm, 'Exposure\Model\SponsorOrganisation', self::NAME));
    }
    
    public function testDuplicate() {
        $this->organisation = new SponsorOrganisation();
        $this->organisation->setName(self::NAME);

        self::$dm->persist($this->organisation);
        $this->setExpectedException('MongoCursorException');
        self::$dm->flush();
    }

    public function tearDown() {
        self::cleanUp();
    }
    
    public static function tearDownAfterClass() {
        self::cleanUp();
    }
    
    public static function cleanUp() {
        self::$dm->clear();
        $organisation = ObjectDocumentMapper::getByName(self::$dm, 'Exposure\Model\SponsorOrganisation', self::NAME);
        if(!is_null($organisation)) {
            self::$dm->remove($organisation);
        }
        
        if (!is_null(self::$logoId)) {
            $logo = ObjectDocumentMapper::getById(self::$dm, 'Sociable\Model\LabelledImage', self::$logoId);
            if(!is_null($logo)) {
                self::$dm->remove($logo);
            }
        }
        
        $soughtTheme = ObjectDocumentMapper::getByLabel(self::$dm, 'Exposure\Model\Theme', self::THEME_LABEL);
        if(!is_null($soughtTheme)) {
            self::$dm->remove($soughtTheme);
        }
        
        if (!is_null(self::$soughtSponsorReturnTypeId)) {
            $logo = ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturnType', self::$soughtSponsorReturnTypeId);
            if(!is_null($logo)) {
                self::$dm->remove($logo);
            }
        }
        
        $sponsorUser = ObjectDocumentMapper::getByEmail(self::$dm, 'Exposure\Model\User', self::SPONSOR_USER_EMAIL);
        if(!is_null($sponsorUser)) {
            self::$dm->remove($sponsorUser);
        }
        
        if (!is_null(self::$notificationId)) {
            $logo = ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorContributionNotification', self::$notificationId);
            if(!is_null($logo)) {
                self::$dm->remove($logo);
            }
        }
        
        self::$dm->flush();
    }

}

?>
