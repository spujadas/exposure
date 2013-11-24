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

use Exposure\Model\SponsorOrganisation,
    Exposure\Model\SponsorContributionTypes,
    Exposure\Model\SponsorReturnType,
    Exposure\Model\User,
    Exposure\Model\SponsorContributionNotification,
    Exposure\Model\ProjectThemeSuggestionNotification,
    Exposure\Model\Theme,
    Sociable\Model\URL,
    Sociable\Model\Organisation,
    Sociable\Model\BusinessSector,
    Sociable\Model\MultiLanguageString,
    Sociable\Utility\StringValidator,
    Sociable\Model\ContactDetails,
    Sociable\Model\WebPresence,
    Sociable\Model\LabelledImage;

class SponsorOrganisationTest extends \PHPUnit_Framework_TestCase {
    protected $sponsorOrganisation;
    
    // from \Sociable
    const NAME = 'Foo Bar Ltd';
    const TYPE = Organisation::BUSINESS_ORGANISATION;
    protected $businessSector;
    
    // from \Exposure
    const URL_SLUG = 'foo-bar-ltd';
    protected $description;
    protected $description_toolong;
    protected $description_empty;
    protected $logo;
    protected $contactDetails;
    protected $webPresence;
    protected $soughtTheme;
    protected $soughtContributionTypes;
    protected $soughtSponsorReturnType;
    protected $sponsorUser;
    protected $notification;
    protected $creationDateTime;

    public function setUp() {
        $this->sponsorOrganisation = new SponsorOrganisation;

        $this->businessSector = new BusinessSector;
        
        $this->description = new MultiLanguageString('foo', 'fr');
        $this->description_toolong = new MultiLanguageString(
                str_repeat('a', SponsorOrganisation::DESCRIPTION_MAX_LENGTH + 1),
                'fr');
        $this->description_empty = new MultiLanguageString('', 'fr');

        $this->logo = new LabelledImage();
        $this->logo->setImageFile('logo.png');
        $this->logo->setMime(LabelledImage::MIME_PNG);
        $this->logo->setDescription(new MultiLanguageString('chaîne de caractères', 'fr'));
        
        $this->contactDetails = new ContactDetails;
        $this->webPresence = new WebPresence;
        $this->webPresence->setType(WebPresence::TYPE_FACEBOOK);
        $this->webPresence->setUrl(new URL('http://facebook.com/foobar'));
        
        $this->soughtTheme = new Theme;
        $this->soughtContributionTypes = new SponsorContributionTypes;
        $this->soughtSponsorReturnType = new SponsorReturnType;
        $this->sponsorUser = new User;
        $this->sponsorUser->setType(User::TYPE_SPONSOR);
        $this->notification = new SponsorContributionNotification;

        $this->creationDateTime = new \DateTime;
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\SponsorOrganisation', $this->sponsorOrganisation);
    }

    public function testGetUrlSlug() {
        $this->sponsorOrganisation->setName(self::NAME);
        $this->assertEquals(self::URL_SLUG, $this->sponsorOrganisation->getUrlSlug());
    }
    
    public function testSetDescription_empty() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_EMPTY);
        $this->sponsorOrganisation->setDescription($this->description_empty);
    }
    
    public function testGetDescription_empty() {
        try {
            $this->sponsorOrganisation->setDescription($this->description_empty);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->sponsorOrganisation->getDescription());
    }
    
    public function testSetDescription_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_TOO_LONG);
        $this->sponsorOrganisation->setDescription($this->description_toolong);
    }
    
    public function testGetDescription_toolong() {
        try {
            $this->sponsorOrganisation->setDescription($this->description_toolong);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->sponsorOrganisation->getDescription());
    }
    
    public function testSetGetDescription() {
        $this->assertEquals($this->description, 
                $this->sponsorOrganisation->setDescription($this->description));
        $this->assertEquals($this->description, $this->sponsorOrganisation->getDescription());
    }

    public function testSetGetLogo() {
        $this->assertEquals($this->logo, 
                $this->sponsorOrganisation->setLogo($this->logo));
        $this->assertEquals($this->logo, 
                $this->sponsorOrganisation->getLogo());
    }
    
    public function testSetGetContactDetails() {
        $this->assertEquals($this->contactDetails, 
                $this->sponsorOrganisation->setContactDetails($this->contactDetails));
        $this->assertEquals($this->contactDetails, 
                $this->sponsorOrganisation->getContactDetails());
    }
    
    public function testAddRemoveWebPresence() {
        $this->assertEquals(0, $this->sponsorOrganisation->getWebPresences()->count());
        $this->sponsorOrganisation->addWebPresence($this->webPresence);
        $this->assertEquals(1, $this->sponsorOrganisation->getWebPresences()->count());
        $this->assertTrue($this->sponsorOrganisation->getWebPresences()->contains($this->webPresence));
        $this->assertTrue($this->sponsorOrganisation->removeWebPresence($this->webPresence));
        $this->assertEquals(0, $this->sponsorOrganisation->getWebPresences()->count());
        $this->assertFalse($this->sponsorOrganisation->getWebPresences()->contains($this->webPresence));
        $this->assertFalse($this->sponsorOrganisation->removeWebPresence($this->webPresence));
    }
    
    public function testAddWebPresence_toomany() {
        for ($i=0; $i<SponsorOrganisation::WEB_PRESENCES_MAX_COUNT; $i++) {
            $this->sponsorOrganisation->addWebPresence($this->webPresence);
        }
        $this->setExpectedException('Exposure\Model\SponsorOrganisationException', 
            SponsorOrganisation::EXCEPTION_TOO_MANY_WEB_PRESENCES);
        $this->sponsorOrganisation->addWebPresence($this->webPresence);
    }
   
    public function testAddRemoveSoughtTheme() {
        $this->assertEquals(0, $this->sponsorOrganisation->getSoughtThemes()->count());
        $this->sponsorOrganisation->addSoughtTheme($this->soughtTheme);
        $this->assertEquals(1, $this->sponsorOrganisation->getSoughtThemes()->count());
        $this->assertTrue($this->sponsorOrganisation->getSoughtThemes()->contains($this->soughtTheme));
        $this->assertTrue($this->sponsorOrganisation->removeSoughtTheme($this->soughtTheme));
        $this->assertEquals(0, $this->sponsorOrganisation->getSoughtThemes()->count());
        $this->assertFalse($this->sponsorOrganisation->getSoughtThemes()->contains($this->soughtTheme));
        $this->assertFalse($this->sponsorOrganisation->removeSoughtTheme($this->soughtTheme));
    }
    
    public function testAddRemoveSoughtSponsorReturnType() {
        $this->assertEquals(0, $this->sponsorOrganisation->getSoughtSponsorReturnTypes()->count());
        $this->sponsorOrganisation->addSoughtSponsorReturnType($this->soughtSponsorReturnType);
        $this->assertEquals(1, $this->sponsorOrganisation->getSoughtSponsorReturnTypes()->count());
        $this->assertTrue($this->sponsorOrganisation->getSoughtSponsorReturnTypes()->contains($this->soughtSponsorReturnType));
        $this->assertTrue($this->sponsorOrganisation->removeSoughtSponsorReturnType($this->soughtSponsorReturnType));
        $this->assertEquals(0, $this->sponsorOrganisation->getSoughtSponsorReturnTypes()->count());
        $this->assertFalse($this->sponsorOrganisation->getSoughtSponsorReturnTypes()->contains($this->soughtSponsorReturnType));
        $this->assertFalse($this->sponsorOrganisation->removeSoughtSponsorReturnType($this->soughtSponsorReturnType));
    }
    
    public function testAddSponsorUser_notasponsor() {
        $this->sponsorUser->setType(User::TYPE_PROJECT_OWNER);
        $this->setExpectedException('Exposure\Model\SponsorOrganisationException', 
            SponsorOrganisation::EXCEPTION_OBJECT_USER_NOT_A_SPONSOR);
        $this->sponsorOrganisation->addSponsorUser($this->sponsorUser);
    }
    
    public function testAddSponsorUser_toomany() {
        for ($i=0; $i<SponsorOrganisation::SPONSOR_USERS_MAX_COUNT; $i++) {
            $this->sponsorOrganisation->addSponsorUser($this->sponsorUser);
        }
        $this->setExpectedException('Exposure\Model\SponsorOrganisationException', 
            SponsorOrganisation::EXCEPTION_TOO_MANY_SPONSOR_USERS);
        $this->sponsorOrganisation->addSponsorUser($this->sponsorUser);
    }
    
    public function testAddRemoveSponsorUser() {
        $this->sponsorOrganisation->addSponsorUser($this->sponsorUser);
        $this->assertEquals(1, $this->sponsorOrganisation->getSponsorUsers()->count());
        $this->assertTrue($this->sponsorOrganisation->getSponsorUsers()->contains($this->sponsorUser));
        $this->assertTrue($this->sponsorOrganisation->removeSponsorUser($this->sponsorUser));
        $this->assertEquals(0, $this->sponsorOrganisation->getSponsorUsers()->count());
        $this->assertFalse($this->sponsorOrganisation->getSponsorUsers()->contains($this->sponsorUser));
        $this->assertFalse($this->sponsorOrganisation->removeSponsorUser($this->sponsorUser));
    }
    
    /* // uncomment when more than 1 user is allowed per sponsor organisation
    public function testAddSponsorUser_duplicateuser() {
        $this->sponsorOrganisation->addSponsorUser($this->sponsorUser);
        $this->setExpectedException('Exposure\Model\SponsorOrganisationException', 
            SponsorOrganisation::EXCEPTION_DUPLICATE_SPONSOR_USER);
        $this->sponsorOrganisation->addSponsorUser($this->sponsorUser);
    }
     */
    
    public function testAddNotification_invalid() {
        $notification = $this->getMockForAbstractClass('Exposure\Model\Notification');
        $this->setExpectedException('Exposure\Model\SponsorOrganisationException', 
                SponsorOrganisation::EXCEPTION_INVALID_NOTIFICATION_TYPE);
        $this->sponsorOrganisation->addNotification($notification);
    }
    
    public function testAddNotification() {
        $this->sponsorOrganisation->addNotification($this->notification);
        $this->assertEquals(1, $this->sponsorOrganisation->getNotifications()->count());
    }
    
    public function testRemoveNotification() {
        $this->assertEquals(0, $this->sponsorOrganisation->getNotifications()->count());
        $this->sponsorOrganisation->addNotification($this->notification);
        $this->assertEquals(1, $this->sponsorOrganisation->getNotifications()->count());
        $dummyNotification = new ProjectThemeSuggestionNotification();
        $this->assertFalse($this->sponsorOrganisation->removeNotification($dummyNotification));
        $this->assertEquals(1, $this->sponsorOrganisation->getNotifications()->count());
        $this->assertTrue($this->sponsorOrganisation->removeNotification($this->notification));
        $this->assertEquals(0, $this->sponsorOrganisation->getNotifications()->count());
    }
    
    public function testValidate_missingname() {
        $this->sponsorOrganisation->setType(self::TYPE);
        $this->sponsorOrganisation->setBusinessSector($this->businessSector);
        $this->sponsorOrganisation->setDescription($this->description);
        $this->sponsorOrganisation->setLogo($this->logo);
        $this->sponsorOrganisation->setSoughtContributionTypes($this->soughtContributionTypes);
        $this->sponsorOrganisation->addSponsorUser($this->sponsorUser);
        $this->sponsorOrganisation->setCreationDateTime($this->creationDateTime);
        $this->setExpectedException('Sociable\Utility\StringException', 
            StringValidator::EXCEPTION_NOT_A_STRING);
        $this->sponsorOrganisation->validate();
    }
    
    public function testValidate_missingtype() {
        $this->sponsorOrganisation->setName(self::NAME);
        $this->sponsorOrganisation->setBusinessSector($this->businessSector);
        $this->sponsorOrganisation->setDescription($this->description);
        $this->sponsorOrganisation->setLogo($this->logo);
        $this->sponsorOrganisation->setSoughtContributionTypes($this->soughtContributionTypes);
        $this->sponsorOrganisation->addSponsorUser($this->sponsorUser);
        $this->sponsorOrganisation->setCreationDateTime($this->creationDateTime);
        $this->setExpectedException('Sociable\Model\OrganisationException', 
                Organisation::EXCEPTION_INVALID_TYPE);
        $this->sponsorOrganisation->validate();
    }
    
    public function testValidate_missingbusinesssector() {
        $this->sponsorOrganisation->setName(self::NAME);
        $this->sponsorOrganisation->setType(self::TYPE);
        $this->sponsorOrganisation->setDescription($this->description);
        $this->sponsorOrganisation->setLogo($this->logo);
        $this->sponsorOrganisation->setSoughtContributionTypes($this->soughtContributionTypes);
        $this->sponsorOrganisation->addSponsorUser($this->sponsorUser);
        $this->sponsorOrganisation->setCreationDateTime($this->creationDateTime);
        $this->setExpectedException('Sociable\Model\OrganisationException', 
            Organisation::EXCEPTION_MISSING_BUSINESS_SECTOR);
        $this->sponsorOrganisation->validate();
    }
    
    public function testValidate_missingdescription() {
        $this->sponsorOrganisation->setName(self::NAME);
        $this->sponsorOrganisation->setType(self::TYPE);
        $this->sponsorOrganisation->setBusinessSector($this->businessSector);
        $this->sponsorOrganisation->setLogo($this->logo);
        $this->sponsorOrganisation->setSoughtContributionTypes($this->soughtContributionTypes);
        $this->sponsorOrganisation->addSponsorUser($this->sponsorUser);
        $this->sponsorOrganisation->setCreationDateTime($this->creationDateTime);
        $this->setExpectedException('Exposure\Model\SponsorOrganisationException', 
                SponsorOrganisation::EXCEPTION_INVALID_DESCRIPTION);
        $this->sponsorOrganisation->validate();
    }
    
    public function testValidate_missinglogo() {
        $this->sponsorOrganisation->setName(self::NAME);
        $this->sponsorOrganisation->setType(self::TYPE);
        $this->sponsorOrganisation->setBusinessSector($this->businessSector);
        $this->sponsorOrganisation->setDescription($this->description);
        $this->sponsorOrganisation->setSoughtContributionTypes($this->soughtContributionTypes);
        $this->sponsorOrganisation->addSponsorUser($this->sponsorUser);
        $this->sponsorOrganisation->setCreationDateTime($this->creationDateTime);
        $this->setExpectedException('Exposure\Model\SponsorOrganisationException', 
                SponsorOrganisation::EXCEPTION_INVALID_LOGO);
        $this->sponsorOrganisation->validate();
    }
    
    public function testValidate_missingsoughtcontributiontypes() {
        $this->sponsorOrganisation->setName(self::NAME);
        $this->sponsorOrganisation->setType(self::TYPE);
        $this->sponsorOrganisation->setBusinessSector($this->businessSector);
        $this->sponsorOrganisation->setDescription($this->description);
        $this->sponsorOrganisation->setLogo($this->logo);
        $this->sponsorOrganisation->addSponsorUser($this->sponsorUser);
        $this->sponsorOrganisation->setCreationDateTime($this->creationDateTime);
        $this->setExpectedException('Exposure\Model\SponsorOrganisationException', 
                SponsorOrganisation::EXCEPTION_INVALID_SOUGHT_CONTRIBUTION_TYPES);
        $this->sponsorOrganisation->validate();
    }
    
    public function testValidate_missingsponsoruser() {
        $this->sponsorOrganisation->setName(self::NAME);
        $this->sponsorOrganisation->setType(self::TYPE);
        $this->sponsorOrganisation->setBusinessSector($this->businessSector);
        $this->sponsorOrganisation->setDescription($this->description);
        $this->sponsorOrganisation->setLogo($this->logo);
        $this->sponsorOrganisation->setSoughtContributionTypes($this->soughtContributionTypes);
        $this->sponsorOrganisation->setCreationDateTime($this->creationDateTime);
        $this->setExpectedException('Exposure\Model\SponsorOrganisationException', 
                SponsorOrganisation::EXCEPTION_NO_SPONSOR_USER);
        $this->sponsorOrganisation->validate();
    }
    
    public function testValidate_missingcreationdatetime() {
        $this->sponsorOrganisation->setName(self::NAME);
        $this->sponsorOrganisation->setType(self::TYPE);
        $this->sponsorOrganisation->setBusinessSector($this->businessSector);
        $this->sponsorOrganisation->setDescription($this->description);
        $this->sponsorOrganisation->setLogo($this->logo);
        $this->sponsorOrganisation->setSoughtContributionTypes($this->soughtContributionTypes);
        $this->sponsorOrganisation->addSponsorUser($this->sponsorUser);
        $this->setExpectedException('Exposure\Model\SponsorOrganisationException', 
                SponsorOrganisation::EXCEPTION_INVALID_CREATION_DATE_TIME);
        $this->sponsorOrganisation->validate();
    }
    
    public function testValidate() {
        $this->sponsorOrganisation->setName(self::NAME);
        $this->sponsorOrganisation->setType(self::TYPE);
        $this->sponsorOrganisation->setBusinessSector($this->businessSector);
        $this->sponsorOrganisation->setDescription($this->description);
        $this->sponsorOrganisation->setLogo($this->logo);
        $this->sponsorOrganisation->setSoughtContributionTypes($this->soughtContributionTypes);
        $this->sponsorOrganisation->addSponsorUser($this->sponsorUser);
        $this->sponsorOrganisation->setCreationDateTime($this->creationDateTime);
        $this->sponsorOrganisation->validate();
        $this->sponsorOrganisation->setContactDetails($this->contactDetails);
        $this->sponsorOrganisation->addWebPresence($this->webPresence);
        $this->sponsorOrganisation->addSoughtTheme($this->soughtTheme);
        $this->sponsorOrganisation->addSoughtSponsorReturnType($this->soughtSponsorReturnType);
        $this->sponsorOrganisation->addNotification($this->notification);
        $this->sponsorOrganisation->validate();
    }

    public function tearDown() {
        unset($this->sponsorOrganisation);
    }

}

?>
