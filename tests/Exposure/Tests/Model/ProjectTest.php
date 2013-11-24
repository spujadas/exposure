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

use Exposure\Model\Project,
    Exposure\Model\ModerationStatus,
    Exposure\Model\ProjectWantNotification,
    Exposure\Model\ApprovableMultiLanguageString,
    Exposure\Model\ApprovableLabelledImage,
    Exposure\Model\Theme,
    Exposure\Model\ProjectThemeSuggestionNotification,
    Sociable\Model\MultiLanguageString,
    Sociable\Model\URL,
    Sociable\Utility\StringValidator,
    Sociable\Model\WebPresence,
    Sociable\Model\Country,
    Sociable\Model\Location,
    Sociable\Model\LabelledImage,
    Sociable\Model\Language,
    Sociable\Utility\NumberValidator;

class ProjectTest extends \PHPUnit_Framework_TestCase {

    protected $project;
    const NAME = 'Foo Bar';
    const URL_SLUG = 'foo-bar';
    protected $moderationStatus;
    const LANGUAGE_CODE = 'fr';
    protected $creationDateTime;
    protected $theme;
    protected $notification;

    protected $summary;
    protected $summary_toolong;
    protected $summary_empty;
    
    protected $description;
    protected $description_toolong;
    protected $description_empty;

    protected $audienceDescription;
    protected $audienceDescription_toolong;
    protected $audienceDescription_empty;
    protected $audienceRange;
    
    protected $photo;

    protected $webPresences;
    protected $country;
    protected $location;
    protected $sponsoringDeadline;
    protected $eventDateTime;

    public function setUp() {
        $this->project = new Project();
        
        $this->moderationStatus = new ModerationStatus();
        $this->moderationStatus->setStatus(ModerationStatus::STATUS_APPROVED);
        $this->moderationStatus->setReasonCode('s');
        $this->moderationStatus->setComment('comment');

        $this->creationDateTime = new \DateTime;
        
        $this->theme = new Theme;
        $this->notification = new ProjectWantNotification;
        
        $moderationStatus = new ModerationStatus();
        $moderationStatus->setReasonCode('code');
        $moderationStatus->setStatus(ModerationStatus::STATUS_APPROVED);

        $this->summary = new ApprovableMultiLanguageString('foo', 'fr');
        $this->summary->setModerationStatus($moderationStatus);
        $this->summary_toolong = new ApprovableMultiLanguageString(
            str_repeat('a', Project::DESCRIPTION_MAX_LENGTH + 1),
            'fr');
        $this->summary_toolong->setModerationStatus($moderationStatus);
        $this->summary_empty = new ApprovableMultiLanguageString('', 'fr');
        $this->summary_empty->setModerationStatus($moderationStatus);

        $this->description = new ApprovableMultiLanguageString('foo', 'fr');
        $this->description->setModerationStatus($moderationStatus);
        $this->description_toolong = new ApprovableMultiLanguageString(
            str_repeat('a', Project::DESCRIPTION_MAX_LENGTH + 1),
            'fr');
        $this->description_toolong->setModerationStatus($moderationStatus);
        $this->description_empty = new ApprovableMultiLanguageString('', 'fr');
        $this->description_empty->setModerationStatus($moderationStatus);

        $this->audienceDescription = new ApprovableMultiLanguageString('foo', 'fr');
        $this->audienceDescription->setModerationStatus($moderationStatus);
        $this->audienceDescription_toolong = new ApprovableMultiLanguageString(
            str_repeat('a', Project::AUDIENCE_DESCRIPTION_MAX_LENGTH + 1),
            'fr');
        $this->audienceDescription_toolong->setModerationStatus($moderationStatus);
        $this->audienceDescription_empty = new ApprovableMultiLanguageString('', 'fr');
        $this->audienceDescription_empty->setModerationStatus($moderationStatus);

        $this->photo = new ApprovableLabelledImage();
        $image = new LabelledImage;
        $image->setImageFile('photo.png');
        $image->setMime(LabelledImage::MIME_PNG);
        $image->setDescription(new MultiLanguageString('chaîne de caractères', 'fr'));
        $this->photo->setCurrent($image);
        $this->photo->setModerationStatus($moderationStatus);
        
        $this->webPresence = new WebPresence;
        $this->webPresence->setType(WebPresence::TYPE_FACEBOOK);
        $this->webPresence->setUrl(new URL('http://facebook.com/foobar'));
        
        $this->country = new Country();
        $this->location = new Location();

        $this->sponsoringDeadline = new \DateTime;
        $this->eventDateTime = new \DateTime;
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\Project', $this->project);
    }
    
    public function testSetName_notastring() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_NOT_A_STRING);
        $this->project->setName(array());
    }
    
    public function testGetName_notastring() {
        try {
            $this->project->setName(array());
        }
        catch (\Exception $e) {}
        $this->assertNull($this->project->getName());
    }
    
    public function testSetName_empty() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_EMPTY);
        $this->project->setName('');
    }
    
    public function testGetName_empty() {
        try {
            $this->project->setName('');
        }
        catch (\Exception $e) {}
        $this->assertNull($this->project->getName());
    }
    
    public function testSetName_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', StringValidator::EXCEPTION_TOO_LONG);
        $this->project->setName(str_repeat('a', Project::NAME_MAX_LENGTH + 1));
    }
    
    public function testGetName_toolong() {
        try {
            $this->project->setName(str_repeat('a', Project::NAME_MAX_LENGTH + 1));
        }
        catch (\Exception $e) {}
        $this->assertNull($this->project->getName());
    }
    
    public function testSetGetName() {
        $this->assertEquals(self::NAME, 
                $this->project->setName(self::NAME));
        $this->assertEquals(self::NAME, $this->project->getName());
    }
    
    public function testGetUrlSlug() {
        $this->project->setName(self::NAME);
        $this->assertEquals(self::URL_SLUG, $this->project->getUrlSlug());
    }
            
    public function testSetGetModerationStatus() {
        $this->assertEquals($this->moderationStatus, 
                $this->project->setModerationStatus($this->moderationStatus));
        $this->assertEquals($this->moderationStatus, 
                $this->project->getModerationStatus());
    }

    public function testSetLanguageCode_notastring() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_NOT_A_STRING);
        $this->project->setLanguageCode(array());
    }
    
    public function testGetLanguageCode_notastring() {
        try {
            $this->project->setLanguageCode(array());
        }
        catch (\Exception $e) {}
        $this->assertNull($this->project->getLanguageCode());
    }
    
    public function testSetLanguageCode_empty() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_EMPTY);
        $this->project->setLanguageCode('');
    }
    
    public function testGetLanguageCode_empty() {
        try {
            $this->project->setLanguageCode('');
        }
        catch (\Exception $e) {}
        $this->assertNull($this->project->getLanguageCode());
    }
    
    public function testSetLanguageCode_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_TOO_LONG);
        $this->project->setLanguageCode(str_repeat('a', Language::CODE_MAX_LENGTH + 1));
    }
    
    public function testGetLanguageCode_toolong() {
        try {
            $this->project->setLanguageCode(str_repeat('a', Language::CODE_MAX_LENGTH + 1));
        }
        catch (\Exception $e) {}
        $this->assertNull($this->project->getLanguageCode());
    }

    public function testSetGetLanguageCode() {
        $this->assertNull($this->project->setLanguageCode(null));
        $this->assertNull($this->project->getLanguageCode());
        $this->assertEquals(self::LANGUAGE_CODE, $this->project->setLanguageCode(self::LANGUAGE_CODE));
        $this->assertEquals(self::LANGUAGE_CODE, $this->project->getLanguageCode());
    }
    
    public function testSetGetCreationDateTime() {
        $this->assertEquals($this->creationDateTime, 
                $this->project->setCreationDateTime($this->creationDateTime));
        $this->assertEquals($this->creationDateTime, $this->project->getCreationDateTime());
    }

    public function testSetGetTheme() {
        $this->assertEquals($this->theme, $this->project->setTheme($this->theme));
        $this->assertEquals($this->theme, $this->project->getTheme());
    }

    public function testAddNotification_invalid() {
        $notification = $this->getMockForAbstractClass('Exposure\Model\Notification');
        $this->setExpectedException('Exposure\Model\ProjectException', 
                Project::EXCEPTION_INVALID_NOTIFICATION_TYPE);
        $this->project->addNotification($notification);
    }

    public function testAddRemoveNotification() {
        $this->assertEquals(0, $this->project->getNotifications()->count());
        $this->project->addNotification($this->notification);
        $this->assertEquals(1, $this->project->getNotifications()->count());
        $dummyNotification = new ProjectThemeSuggestionNotification();
        $this->assertFalse($this->project->removeNotification($dummyNotification));
        $this->assertEquals(1, $this->project->getNotifications()->count());
        $this->assertTrue($this->project->removeNotification($this->notification));
        $this->assertEquals(0, $this->project->getNotifications()->count());
    }
    
    public function testSetSummary_empty() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_EMPTY);
        $this->project->setSummary($this->summary_empty);
    }
    
    public function testGetSummary_empty() {
        try {
            $this->project->setSummary($this->summary_empty);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->project->getSummary());
    }
    
    public function testSetSummary_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_TOO_LONG);
        $this->project->setSummary($this->summary_toolong);
    }
    
    public function testGetSummary_toolong() {
        try {
            $this->project->setSummary($this->summary_toolong);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->project->getSummary());
    }
    
    public function testSetGetSummary() {
        $this->assertEquals($this->summary, 
                $this->project->setSummary($this->summary));
        $this->assertEquals($this->summary, $this->project->getSummary());
    }

    public function testSetDescription_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_TOO_LONG);
        $this->project->setDescription($this->description_toolong);
    }
    
    public function testGetDescription_toolong() {
        try {
            $this->project->setDescription($this->description_toolong);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->project->getDescription());
    }
    
    public function testSetGetDescription() {
        $this->assertEquals($this->description_empty, 
                $this->project->setDescription($this->description_empty));
        $this->assertEquals($this->description_empty, $this->project->getDescription());
        $this->assertEquals($this->description, 
                $this->project->setDescription($this->description));
        $this->assertEquals($this->description, $this->project->getDescription());
    }

    public function testSetAudienceDescription_empty() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_EMPTY);
        $this->project->setAudienceDescription($this->audienceDescription_empty);
    }
    
    public function testGetAudienceDescription_empty() {
        try {
            $this->project->setAudienceDescription($this->audienceDescription_empty);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->project->getAudienceDescription());
    }
    
    public function testSetAudienceDescription_toolong() {
        $this->setExpectedException('Sociable\Utility\StringException', 
                StringValidator::EXCEPTION_TOO_LONG);
        $this->project->setAudienceDescription($this->audienceDescription_toolong);
    }
    
    public function testGetAudienceDescription_toolong() {
        try {
            $this->project->setAudienceDescription($this->audienceDescription_toolong);
        }
        catch (\Exception $e) {}
        $this->assertNull($this->project->getAudienceDescription());
    }
    
    public function testSetGetAudienceDescription() {
        $this->assertEquals($this->audienceDescription, 
                $this->project->setAudienceDescription($this->audienceDescription));
        $this->assertEquals($this->audienceDescription, $this->project->getAudienceDescription());
    }

    public function testSetAudienceRange_notanumber() {
        $this->setExpectedException('Sociable\Utility\NumberException', 
                NumberValidator::EXCEPTION_NOT_A_NUMBER);
        $this->project->setAudienceRange(array(), array());
    }
    
    public function testGetAudienceRange_notanumber() {
        try {
            $this->project->setAudienceRange(array(), array());
        }
        catch (\Exception $e) {}
        $this->assertEquals(array('min'=>0, 'max'=>0), $this->project->getAudienceRange());
    }
    
    public function testSetAudienceRange_notaninteger() {
        $this->setExpectedException('Sociable\Utility\NumberException', 
                NumberValidator::EXCEPTION_NOT_AN_INTEGER);
        $this->project->setAudienceRange(1.2, 1.2);
    }
    
    public function testGetAudienceRange_notaninteger() {
        try {
            $this->project->setAudienceRange(1.2, 1.2);
        }
        catch (\Exception $e) {}
        $this->assertEquals(array('min'=>0, 'max'=>0), $this->project->getAudienceRange());
    }
    
    public function testSetAudienceRange_notpositive() {
        $this->setExpectedException('Sociable\Utility\NumberException', 
                NumberValidator::EXCEPTION_NOT_POSITIVE);
        $this->project->setAudienceRange(-1, -1);
    }
    
    public function testGetAudienceRange_notpositive() {
        try {
            $this->project->setAudienceRange(-1, -1);
        }
        catch (\Exception $e) {}
        $this->assertEquals(array('min'=>0, 'max'=>0), $this->project->getAudienceRange());
    }
    
    public function testSetAudienceRange_toolarge() {
        $this->setExpectedException('Sociable\Utility\NumberException', 
                NumberValidator::EXCEPTION_TOO_LARGE);
        $this->project->setAudienceRange(1, Project::AUDIENCE_RANGE_MAX + 1);
    }
    
    public function testGetAudienceRange_toolarge() {
        try {
            $this->project->setAudienceRange(1, Project::AUDIENCE_RANGE_MAX + 1);
        }
        catch (\Exception $e) {}
        $this->assertEquals(array('min'=>0, 'max'=>0), $this->project->getAudienceRange());
    }
    
    public function testSetAudienceRange_minLargerThanMax() {
        $this->setExpectedException('Exposure\Model\ProjectException', 
                Project::EXCEPTION_INVALID_AUDIENCE_RANGE);
        $this->project->setAudienceRange(2, 1);
    }
    
    public function testGetAudienceRange_minLargerThanMax() {
        try {
            $this->project->setAudienceRange(2, 1);
        }
        catch (\Exception $e) {}
        $this->assertEquals(array('min'=>0, 'max'=>0), $this->project->getAudienceRange());
    }
    
    public function testSetGetAudienceRange() {
        $this->assertEquals(array('min'=>1, 'max'=>2),
                $this->project->setAudienceRange(1, 2));
        $this->assertEquals(array('min'=>1, 'max'=>2),
                $this->project->getAudienceRange());
    }

    public function testAddRemovePhoto() {
        $this->assertEquals(0, $this->project->getPhotos()->count());
        $this->project->addPhoto($this->photo);
        $this->assertEquals(1, $this->project->getPhotos()->count());
        $dummyPhoto = new ApprovableLabelledImage();
        $this->assertFalse($this->project->removePhoto($dummyPhoto));
        $this->assertEquals(1, $this->project->getPhotos()->count());
        $this->assertTrue($this->project->removePhoto($this->photo));
        $this->assertEquals(0, $this->project->getPhotos()->count());
    }

    public function testAddPhoto_toomany() {
        for ($i=0; $i<Project::PHOTOS_MAX_COUNT; $i++) {
            $this->project->addPhoto($this->photo);
        }
        $this->setExpectedException('Exposure\Model\ProjectException', 
            Project::EXCEPTION_TOO_MANY_PHOTOS);
        $this->project->addPhoto($this->photo);

    }

    public function testAddRemoveWebPresence() {
        $this->assertEquals(0, $this->project->getWebPresences()->count());
        $this->project->addWebPresence($this->webPresence);
        $this->assertEquals(1, $this->project->getWebPresences()->count());
        $this->assertTrue($this->project->getWebPresences()->contains($this->webPresence));
        $this->assertTrue($this->project->removeWebPresence($this->webPresence));
        $this->assertEquals(0, $this->project->getWebPresences()->count());
        $this->assertFalse($this->project->getWebPresences()->contains($this->webPresence));
        $this->assertFalse($this->project->removeWebPresence($this->webPresence));
    }

    public function testSetGetPlace() {
        $this->assertEquals($this->location, $this->project->setPlace($this->location));
        $this->assertEquals($this->location, $this->project->getPlace());
        $this->assertEquals($this->country, $this->project->setPlace($this->country));
        $this->assertEquals($this->country, $this->project->getPlace());
        $this->assertNull($this->project->setPlace(null));
        $this->assertNull($this->project->getPlace());
    }

    public function testSetGetSponsoringDeadline() {
        $this->assertNull($this->project->setSponsoringDeadline());
        $this->assertNull($this->project->getSponsoringDeadline());
        $this->assertEquals($this->sponsoringDeadline, 
                $this->project->setSponsoringDeadline($this->sponsoringDeadline));
        $this->assertEquals($this->sponsoringDeadline, $this->project->getSponsoringDeadline());
    }

    public function testSetGetEventDateTime() {
        $this->assertNull($this->project->setEventDateTime());
        $this->assertNull($this->project->getEventDateTime());
        $this->assertEquals($this->eventDateTime, 
                $this->project->setEventDateTime($this->eventDateTime));
        $this->assertEquals($this->eventDateTime, $this->project->getEventDateTime());
    }

    public function testGetIncreaseResetPageviews() {
        $this->assertEquals(0, $this->project->getPageviews());
        $this->assertEquals(1, $this->project->increasePageviews());
        $this->assertEquals(1, $this->project->getPageviews());
        $this->project->resetPageviews();
        $this->assertEquals(0, $this->project->getPageviews());
    }


    public function testValidate_missingname() {
        $this->project->setModerationStatus($this->moderationStatus);
        $this->project->setCreationDateTime($this->creationDateTime);
        $this->project->setTheme($this->theme);
        $this->project->setSummary($this->summary);
        $this->project->setDescription($this->description);
        $this->project->setAudienceDescription($this->audienceDescription);
        $this->project->setPlace($this->location);
        $this->project->addPhoto($this->photo);
        $this->setExpectedException('Sociable\Utility\StringException', 
            StringValidator::EXCEPTION_NOT_A_STRING);
        $this->project->validate();
    }

    public function testValidate_missingmoderationstatus() {
        $this->project->setName(self::NAME);
        $this->project->setCreationDateTime($this->creationDateTime);
        $this->project->setTheme($this->theme);
        $this->project->setSummary($this->summary);
        $this->project->setDescription($this->description);
        $this->project->setAudienceDescription($this->audienceDescription);
        $this->project->setPlace($this->location);
        $this->project->addPhoto($this->photo);
        $this->setExpectedException('Exposure\Model\ProjectException', 
            Project::EXCEPTION_INVALID_MODERATION_STATUS);
        $this->project->validate();
    }

    public function testValidate_missingcreationdatetime() {
        $this->project->setName(self::NAME);
        $this->project->setModerationStatus($this->moderationStatus);
        $this->project->setTheme($this->theme);
        $this->project->setSummary($this->summary);
        $this->project->setDescription($this->description);
        $this->project->setAudienceDescription($this->audienceDescription);
        $this->project->setPlace($this->location);
        $this->project->addPhoto($this->photo);
        $this->setExpectedException('Exposure\Model\ProjectException', 
            Project::EXCEPTION_INVALID_CREATION_DATE_TIME);
        $this->project->validate();
    }

    public function testValidate_missingtheme() {
        $this->project->setName(self::NAME);
        $this->project->setModerationStatus($this->moderationStatus);
        $this->project->setCreationDateTime($this->creationDateTime);
        $this->project->setSummary($this->summary);
        $this->project->setDescription($this->description);
        $this->project->setAudienceDescription($this->audienceDescription);
        $this->project->setPlace($this->location);
        $this->project->addPhoto($this->photo);
        $this->setExpectedException('Exposure\Model\ProjectException', 
            Project::EXCEPTION_INVALID_THEME);
        $this->project->validate();
    }

    public function testValidate_missingsummary() {
        $this->project->setName(self::NAME);
        $this->project->setModerationStatus($this->moderationStatus);
        $this->project->setCreationDateTime($this->creationDateTime);
        $this->project->setTheme($this->theme);
        $this->project->setDescription($this->description);
        $this->project->setAudienceDescription($this->audienceDescription);
        $this->project->setPlace($this->location);
        $this->project->addPhoto($this->photo);
        $this->setExpectedException('Exposure\Model\ProjectException', 
            Project::EXCEPTION_INVALID_SUMMARY);
        $this->project->validate();
    }

    public function testValidate_missingdescription() {
        $this->project->setName(self::NAME);
        $this->project->setModerationStatus($this->moderationStatus);
        $this->project->setCreationDateTime($this->creationDateTime);
        $this->project->setTheme($this->theme);
        $this->project->setAudienceDescription($this->audienceDescription);
        $this->project->setSummary($this->summary);
        $this->project->setPlace($this->location);
        $this->project->addPhoto($this->photo);
        $this->setExpectedException('Exposure\Model\ProjectException', 
            Project::EXCEPTION_INVALID_DESCRIPTION);
        $this->project->validate();
    }

    public function testValidate_missingaudiencedescription() {
        $this->project->setName(self::NAME);
        $this->project->setModerationStatus($this->moderationStatus);
        $this->project->setCreationDateTime($this->creationDateTime);
        $this->project->setTheme($this->theme);
        $this->project->setDescription($this->description);
        $this->project->setSummary($this->summary);
        $this->project->setPlace($this->location);
        $this->project->addPhoto($this->photo);
        $this->setExpectedException('Exposure\Model\ProjectException', 
            Project::EXCEPTION_INVALID_AUDIENCE_DESCRIPTION);
        $this->project->validate();
    }

    public function testValidate_missingphoto() {
        $this->project->setName(self::NAME);
        $this->project->setModerationStatus($this->moderationStatus);
        $this->project->setCreationDateTime($this->creationDateTime);
        $this->project->setTheme($this->theme);
        $this->project->setSummary($this->summary);
        $this->project->setDescription($this->description);
        $this->project->setAudienceDescription($this->audienceDescription);
        $this->project->setPlace($this->location);
        $this->setExpectedException('Exposure\Model\ProjectException', 
            Project::EXCEPTION_MISSING_PHOTO);
        $this->project->validate();
    }
    
    public function testValidate() {
        $this->project->setName(self::NAME);
        $this->project->setModerationStatus($this->moderationStatus);
        $this->project->setCreationDateTime($this->creationDateTime);
        $this->project->setTheme($this->theme);
        $this->project->setSummary($this->summary);
        $this->project->setDescription($this->description);
        $this->project->setAudienceDescription($this->audienceDescription);
        $this->project->setPlace($this->location);
        $this->project->addPhoto($this->photo);
        $this->project->validate();
        $this->project->setLanguageCode(self::LANGUAGE_CODE);
        $this->project->addNotification($this->notification);
        $this->project->addWebPresence($this->webPresence);
        $this->project->setSponsoringDeadline($this->sponsoringDeadline);
        $this->project->setEventDateTime($this->eventDateTime);
        $this->project->validate();
    }

    public function tearDown() {
        unset($this->project);
    }

}

?>
