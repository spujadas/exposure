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

use Exposure\Model\SponsorContribution,
    Exposure\Model\SponsorOrganisation,
    Sociable\ODM\ObjectDocumentMapper;

class SponsorContributionODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $sponsorContribution;
    protected static $id = null;
    
    protected $contributor;
    const SPONSOR_CONTRIBUTOR_NAME = 'ZZZZZZ_org_name';
    
    const STATUS = SponsorContribution::STATUS_PROPOSAL_APPROVED;
    
    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanUp();

        $this->contributor = new SponsorOrganisation;
        $this->contributor->setName(self::SPONSOR_CONTRIBUTOR_NAME);
        
        $this->sponsorContribution = new SponsorContribution;
        $this->sponsorContribution->setStatus(self::STATUS);
        $this->sponsorContribution->setContributor($this->contributor);
        $this->sponsorContribution->validate();

        self::$dm->persist($this->sponsorContribution);
        self::$dm->persist($this->contributor);
        self::$dm->flush();
        
        self::$id = $this->sponsorContribution->getId();

        self::$dm->clear();
    }

    public function testFound() {
        $this->sponsorContribution = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorContribution', self::$id);
        $this->assertNotNull($this->sponsorContribution);
        $this->assertInstanceOf('Exposure\Model\SponsorContribution', $this->sponsorContribution);
    }
    
    public function testIsValid() {
        $this->sponsorContribution = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorContribution', self::$id);
        $this->sponsorContribution->validate();
    }
   
    public function testIsEqual() {
        $sponsorContribution = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorContribution', self::$id);
        $this->assertEquals(self::SPONSOR_CONTRIBUTOR_NAME, $sponsorContribution->getContributor()->getName());
        $this->assertEquals($this->sponsorContribution->getId(), 
                $sponsorContribution->getContributor()->getContributions()[0]->getId());
    }
   
    public function testRemove() {
        $this->sponsorContribution = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorContribution', self::$id);
        self::$dm->remove($this->sponsorContribution);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorContribution', self::$id));
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
            $sponsorContribution = 
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorContribution', self::$id);
            if(!is_null($sponsorContribution)) {
                self::$dm->remove($sponsorContribution);
            }
        }
        $contributor = ObjectDocumentMapper::getByName(self::$dm, 'Exposure\Model\SponsorOrganisation', self::SPONSOR_CONTRIBUTOR_NAME);
        if(!is_null($contributor)) {
            self::$dm->remove($contributor);
        }
        self::$dm->flush();
    }
}

?>
