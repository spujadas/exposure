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

use Exposure\Model\NonFinancialNeed,
    Exposure\Model\SponsorContribution,
    Exposure\Model\Project,
    Exposure\Model\SponsorReturn,
    Sociable\ODM\ObjectDocumentMapper,
    Sociable\Model\MultiLanguageString;

class NonFinancialNeedODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $nonFinancialNeed;
    protected static $id = null;
    const TYPE = NonFinancialNeed::TYPE_SERVICE;
    
    protected $description;
    const DESCRIPTION_STRING = 'description';
    const DESCRIPTION_LANGUAGE = 'fr';
    
    protected $contribution;
    protected static $contributionId = null;
    
    protected $return;
    protected static $returnId = null;
    
    protected $project;
    const PROJECT_NAME = 'ZZZZZZ_project_name';
    
    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanUp();
        
        $this->contribution = new SponsorContribution;
        $this->return = new SponsorReturn;
        $this->project = new Project;
        $this->project->setName(self::PROJECT_NAME);
        
        $this->nonFinancialNeed = new NonFinancialNeed;
        $this->nonFinancialNeed->setType(self::TYPE);
        
        $this->description = new MultiLanguageString('description', 'fr');
        $this->nonFinancialNeed->setDescription($this->description);
        $this->nonFinancialNeed->setContribution($this->contribution);
        $this->nonFinancialNeed->setReturn($this->return);
        $this->nonFinancialNeed->setProject($this->project);        
        $this->nonFinancialNeed->validate();

        self::$dm->persist($this->nonFinancialNeed);
        self::$dm->persist($this->contribution);
        self::$dm->persist($this->return);
        self::$dm->persist($this->project);
        self::$dm->flush();
        
        self::$id = $this->nonFinancialNeed->getId();
        self::$contributionId = $this->contribution->getId();
        self::$returnId = $this->return->getId();

        self::$dm->clear();
    }

    public function testFound() {
        $this->nonFinancialNeed = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\NonFinancialNeed', self::$id);
        $this->assertNotNull($this->nonFinancialNeed);
        $this->assertInstanceOf('Exposure\Model\NonFinancialNeed', $this->nonFinancialNeed);
    }
    
    public function testIsValid() {
        $this->nonFinancialNeed = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\NonFinancialNeed', self::$id);
        $this->nonFinancialNeed->validate();
    }
   
    public function testIsEqual() {
        $nonFinancialNeed = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\NonFinancialNeed', self::$id);
        $this->assertEquals($this->nonFinancialNeed->getType(), $nonFinancialNeed->getType());
        $this->assertEquals($this->nonFinancialNeed->getDescription()->getStringByLanguageCode('fr'), 
                $nonFinancialNeed->getDescription()->getStringByLanguageCode('fr'));
        $this->assertEquals(self::$contributionId, $nonFinancialNeed->getContribution()->getId());
        $this->assertEquals(self::$returnId, $nonFinancialNeed->getReturn()->getId());
        $this->assertEquals(self::PROJECT_NAME, $nonFinancialNeed->getProject()->getName());
        $this->assertEquals(self::$id, $nonFinancialNeed->getContribution()
                ->getContributedNonFinancialNeed()->getId());
        $this->assertEquals(self::$id, $nonFinancialNeed->getReturn()
                ->getReturnedNonFinancialNeed()->getId());
        $this->assertEquals(self::$id, $nonFinancialNeed->getProject()
                ->getNonFinancialNeeds()[0]->getId());
    }
   
    public function testRemove() {
        $this->nonFinancialNeed = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\NonFinancialNeed', self::$id);
        self::$dm->remove($this->nonFinancialNeed);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\NonFinancialNeed', self::$id));
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
            $nonFinancialNeed = 
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\NonFinancialNeed', self::$id);
            if(!is_null($nonFinancialNeed)) {
                self::$dm->remove($nonFinancialNeed);
            }
        }
        if (!is_null(self::$contributionId)) {
            $contribution = 
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorContribution', self::$contributionId);
            if(!is_null($contribution)) {
                self::$dm->remove($contribution);
            }
        }
        if (!is_null(self::$returnId)) {
            $return = 
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SponsorReturn', self::$returnId);
            if(!is_null($return)) {
                self::$dm->remove($return);
            }
        }
        $project = ObjectDocumentMapper::getByName(self::$dm, 'Exposure\Model\Project', self::PROJECT_NAME);
        if(!is_null($project)) {
            self::$dm->remove($project);
        }
        self::$dm->flush();
    }
}

?>
