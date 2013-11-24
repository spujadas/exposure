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

use Exposure\Model\Project,
    Exposure\Model\ProjectWantNotification,
    Exposure\Model\ModerationStatus,
    Exposure\Model\Theme,
    Exposure\Model\ApprovableLabelledImage,
    Exposure\Model\ApprovableMultiLanguageString,
    Sociable\Model\Location,
    Sociable\Model\MultiLanguageString,
    Sociable\Model\LabelledImage,
    Sociable\Model\WebPresence,
    Sociable\ODM\ObjectDocumentMapper;

class ProjectODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $project;
    protected static $id = null;
    
    const PROJECT_NAME = 'ZZZZZZ_name';
    const URL_SLUG = 'zzzzzz-name';
    
    protected $moderationStatus;
    const LANGUAGE_CODE = 'fr';
    protected $creationDateTime;
    protected $theme;
    const THEME_LABEL = 'ZZZZZ';
    const THEME_NAME_STRING = 'ZZZZZ';
    const THEME_NAME_LANGUAGE = 'fr';
    
    protected $notification;
    protected static $notificationId = null;
    
    protected $summary;
    const SUMMARY_CURRENT_STRING = 'current';
    const SUMMARY_LANGUAGE_CODE = 'fr';
    
    protected $description;
    const DESCRIPTION_CURRENT_STRING = 'current';
    const DESCRIPTION_LANGUAGE_CODE = 'fr';
    
    protected $audienceDescription;
    const AUDIENCE_DESCRIPTION_CURRENT_STRING = 'current';
    const AUDIENCE_DESCRIPTION_LANGUAGE_CODE = 'fr';

    const AUDIENCE_RANGE_MIN = 10;
    const AUDIENCE_RANGE_MAX = 100;
    
    protected $photo;
    protected $currentPhoto;
    const CURRENT_FILE_NAME = 'photo.png';
    protected static $currentId = null;
    protected static $photoId = null;
    
    protected $webPresence;
    
    protected $location;
    const LOCATION_LABEL = 'ZZZZZZ_location_label';
    
    protected $sponsoringDeadline;
    protected $eventDateTime;
    
    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }

    public function setUp() {
        self::cleanup();
        
        $this->project = new Project;
        $this->project->setName(self::PROJECT_NAME);
        $this->project->generateUrlSlug();
        
        $this->moderationStatus = new ModerationStatus();
        $this->moderationStatus->setStatus(ModerationStatus::STATUS_APPROVED);
        $this->moderationStatus->setReasonCode('s');
        $this->moderationStatus->setComment('comment');
        $this->project->setModerationStatus($this->moderationStatus);
        
        $this->project->setLanguageCode(self::LANGUAGE_CODE);
        
        $this->creationDateTime = new \DateTime;
        $this->project->setCreationDateTime($this->creationDateTime);
        
        $this->theme = new Theme;
        $this->theme->setLabel(self::THEME_LABEL);
        $this->themeName = new MultiLanguageString(self::THEME_NAME_STRING, 
                self::THEME_NAME_LANGUAGE);
        $this->theme->setName($this->themeName);
        $this->project->setTheme($this->theme);
        
        $this->notification = new ProjectWantNotification;
        $this->project->addNotification($this->notification);
        
        $status = new ModerationStatus();
        $status->setReasonCode('code');
        $status->setStatus(ModerationStatus::STATUS_APPROVED);
        
        $this->summary = new ApprovableMultiLanguageString(self::SUMMARY_CURRENT_STRING, 
                self::SUMMARY_LANGUAGE_CODE);
        $this->summary->setModerationStatus($status);
        $this->project->setSummary($this->summary);
        
        $this->description = new ApprovableMultiLanguageString(self::DESCRIPTION_CURRENT_STRING,
                self::DESCRIPTION_LANGUAGE_CODE);
        $this->description->setModerationStatus($status);
        $this->project->setDescription($this->description);
        
        $this->audienceDescription = new ApprovableMultiLanguageString(
            self::AUDIENCE_DESCRIPTION_CURRENT_STRING,
            self::AUDIENCE_DESCRIPTION_LANGUAGE_CODE);
        $this->audienceDescription->setModerationStatus($status);
        $this->project->setAudienceDescription($this->audienceDescription);
        
        $this->project->setAudienceRange(self::AUDIENCE_RANGE_MIN, self::AUDIENCE_RANGE_MAX);

        $this->currentPhoto = new LabelledImage;
        $this->currentPhoto->setImageFile(__DIR__ . '/' . self::CURRENT_FILE_NAME);
        $this->currentPhoto->setMime(LabelledImage::MIME_PNG);
        $this->currentPhoto->setDescription(new MultiLanguageString('chaîne de caractères', 'fr'));
        $this->photo = new ApprovableLabelledImage();
        $this->photo->setCurrent($this->currentPhoto);
        $this->photo->setModerationStatus($status);
        $this->project->addPhoto($this->photo);
        
        $this->webPresence = new WebPresence;
        $this->project->addWebPresence($this->webPresence);
        
        $this->location = new Location;
        $this->location->setLabel(self::LOCATION_LABEL);
        $this->project->setPlace($this->location);
        
        $this->sponsoringDeadline = new \DateTime;
        $this->project->setSponsoringDeadline($this->sponsoringDeadline);
        
        $this->eventDateTime = new \DateTime;
        $this->project->setEventDateTime($this->eventDateTime);
        
        $this->project->increasePageviews();

        $this->project->validate();
        
        self::$dm->persist($this->project);
        self::$dm->persist($this->theme);
        self::$dm->persist($this->notification);
        self::$dm->persist($this->photo);
        self::$dm->persist($this->currentPhoto);
        self::$dm->persist($this->location);
        
        self::$dm->flush();
        
        self::$id = $this->project->getId();
        self::$notificationId = $this->notification->getId();
        self::$currentId = $this->currentPhoto->getId();
        self::$photoId = $this->photo->getId();
        
        self::$dm->clear();
    }

    public function testFound() {
        $this->project = ObjectDocumentMapper::getByName(self::$dm, 'Exposure\Model\Project', self::PROJECT_NAME);
        $this->assertNotNull($this->project);
    }
    
    public function testIsValid() {
        $this->project = ObjectDocumentMapper::getByName(self::$dm, 'Exposure\Model\Project', self::PROJECT_NAME);
        $this->project->validate();
    }
   
    public function testIsEqual() {
        $this->project = ObjectDocumentMapper::getByName(self::$dm, 'Exposure\Model\Project', self::PROJECT_NAME);
        
        $this->assertEquals(self::PROJECT_NAME, $this->project->getName());
        $this->assertEquals(self::URL_SLUG, $this->project->getUrlSlug());
        $this->assertEquals($this->moderationStatus, $this->project->getModerationStatus());
        $this->assertEquals(self::LANGUAGE_CODE, $this->project->getLanguageCode());
        $this->assertEquals($this->creationDateTime, $this->project->getCreationDateTime());
        $this->assertEquals(self::THEME_LABEL, $this->project->getTheme()->getLabel());
        $this->assertEquals(self::THEME_NAME_STRING, $this->project->getTheme()->getName()
                ->getStringByLanguageCode(self::THEME_NAME_LANGUAGE));
        $this->assertEquals(self::$notificationId, $this->project->getNotifications()[0]->getId());
        $this->assertEquals(self::SUMMARY_CURRENT_STRING, $this->project->
                getSummary()->getCurrent()->
                getStringByLanguageCode(self::SUMMARY_LANGUAGE_CODE));
        $this->assertEquals(self::DESCRIPTION_CURRENT_STRING, $this->project->
                getDescription()->getCurrent()->
                getStringByLanguageCode(self::DESCRIPTION_LANGUAGE_CODE));
        $this->assertEquals(self::AUDIENCE_DESCRIPTION_CURRENT_STRING, $this->project->
                getAudienceDescription()->getCurrent()->
                getStringByLanguageCode(self::AUDIENCE_DESCRIPTION_LANGUAGE_CODE));
        $this->assertEquals(
            array('min'=>self::AUDIENCE_RANGE_MIN, 'max'=>self::AUDIENCE_RANGE_MAX), 
                $this->project->getAudienceRange());
        $this->assertEquals(self::$currentId, $this->project->getPhotos()[0]->getCurrent()->getId());
        $this->assertEquals($this->webPresence, $this->project->getWebPresences()[0]);
        $this->assertEquals(self::LOCATION_LABEL, $this->project->getPlace()->getLabel());
        $this->assertEquals($this->sponsoringDeadline, $this->project->getSponsoringDeadline());
        $this->assertEquals($this->eventDateTime, $this->project->getEventDateTime());
        $this->assertEquals(1, $this->project->getPageviews());
    }
   
    public function testRemove() {
        $this->project = ObjectDocumentMapper::getByName(self::$dm, 'Exposure\Model\Project', self::PROJECT_NAME);
        self::$dm->remove($this->project);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getByName(self::$dm, 'Exposure\Model\Project', self::PROJECT_NAME));
//        $this->assertNull(Project::getByName(self::PROJECT_NAME, self::$dm));
    }
    
    public function testDuplicate() {
        $this->project = new Project();
        $this->project->setName(self::PROJECT_NAME);
        self::$dm->persist($this->project);
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
        
        $project = ObjectDocumentMapper::getByName(self::$dm, 'Exposure\Model\Project', self::PROJECT_NAME);
        if(!is_null($project)) {
            self::$dm->remove($project);
        }
        
        $theme = ObjectDocumentMapper::getByLabel(self::$dm, 'Exposure\Model\Theme', self::THEME_LABEL);
        if(!is_null($theme)) {
            self::$dm->remove($theme);
        }
        
        if (!is_null(self::$notificationId)) {
            $notification = ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\ProjectWantNotification', self::$notificationId);
            if(!is_null($notification)) {
                self::$dm->remove($notification);
            }
        }
        
        if (!is_null(self::$currentId)) {
            $current = ObjectDocumentMapper::getById(self::$dm, 'Sociable\Model\LabelledImage', self::$currentId);
            if(!is_null($current)) {
                self::$dm->remove($current);
            }
        }
        if (!is_null(self::$photoId)) {
            $photo = ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\ApprovableLabelledImage', self::$photoId);
            if(!is_null($photo)) {
                self::$dm->remove($photo);
            }
        }
        
        $location = ObjectDocumentMapper::getByLabel(self::$dm, 'Sociable\Model\Location', self::LOCATION_LABEL);
        if(!is_null($location)) {
            self::$dm->remove($location);
        }
        
        self::$dm->flush();
    }

}

?>
