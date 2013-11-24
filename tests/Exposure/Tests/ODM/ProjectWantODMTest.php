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

use Exposure\Model\ProjectWant,
    Exposure\Model\Project,
    Exposure\Model\SponsorOrganisation,
    Sociable\ODM\ObjectDocumentMapper;

class ProjectWantODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $projectWant;
    protected static $id = null;
    protected $dateTime;
    
    protected $project;
    const PROJECT_NAME = 'PROJECT_NAME';
    
    protected $sponsorOrganisation;
    const ORGANISATION_NAME = 'ORGANISATION_NAME';
    
    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanUp();

        $this->dateTime = new \DateTime();
        $this->project = new Project;
        $this->project->setName(self::PROJECT_NAME);

        $this->sponsorOrganisation = new SponsorOrganisation;
        $this->sponsorOrganisation->setName(self::ORGANISATION_NAME);
        
        $this->projectWant = new ProjectWant;
        $this->projectWant->setDateTime($this->dateTime);
        $this->projectWant->setProject($this->project);
        $this->projectWant->setSponsorOrganisation($this->sponsorOrganisation);
        $this->projectWant->validate();

        self::$dm->persist($this->projectWant);
        self::$dm->persist($this->sponsorOrganisation);
        self::$dm->persist($this->project);
        self::$dm->flush();
        
        self::$id = $this->projectWant->getId();

        self::$dm->clear();
    }

    public function testFound() {
        $this->projectWant = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\ProjectWant', self::$id);
        $this->assertNotNull($this->projectWant);
        $this->assertInstanceOf('Exposure\Model\ProjectWant', $this->projectWant);
    }
    
    public function testIsValid() {
        $this->projectWant = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\ProjectWant', self::$id);
        $this->projectWant->validate();
    }
   
    public function testIsEqual() {
        $projectWant = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\ProjectWant', self::$id);
        $this->assertEquals($this->projectWant->getDateTime(), $projectWant->getDateTime());
        $this->assertEquals($this->projectWant->getRating(), $projectWant->getRating());
        $this->assertEquals($this->sponsorOrganisation->getId(), $projectWant->getSponsorOrganisation()->getId());
        $this->assertEquals($this->project->getId(), $projectWant->getProject()->getId());
        $this->assertEquals($this->project->getId(), 
                $projectWant->getSponsorOrganisation()->getWants()[0]->getProject()->getId());
        $this->assertEquals($this->projectWant->getId(), 
                $projectWant->getProject()->getWants()[0]->getId());
    }
   
    public function testRemove() {
        $this->projectWant = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\ProjectWant', self::$id);
        self::$dm->remove($this->projectWant);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\ProjectWant', self::$id));
    }
    
    public function tearDown() {
        self::cleanUp();
    }
    
    public static function tearDownAfterClass() {
        self::cleanUp();
    }

    public static function cleanUp() {
        self::$dm->clear();
        if (!is_null(self::$id)) {
            $projectWant = 
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\ProjectWant', self::$id);
            if(!is_null($projectWant)) {
                self::$dm->remove($projectWant);
            }
        }
        $sponsorOrganisation = 
                ObjectDocumentMapper::getByName(self::$dm, 'Exposure\Model\SponsorOrganisation', self::ORGANISATION_NAME);
        if(!is_null($sponsorOrganisation)) {
            self::$dm->remove($sponsorOrganisation);
        }
        $project = 
                ObjectDocumentMapper::getByName(self::$dm, 'Exposure\Model\Project', self::PROJECT_NAME);
        if(!is_null($project)) {
            self::$dm->remove($project);
        }
        self::$dm->flush();
    }
}

?>
