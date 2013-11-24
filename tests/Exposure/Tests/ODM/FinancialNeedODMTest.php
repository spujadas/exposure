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

use Exposure\Model\FinancialNeed,
    Exposure\Model\FinancialNeedByAmount,
    Exposure\Model\Project,
    Sociable\ODM\ObjectDocumentMapper,
    Sociable\Model\MultiCurrencyValue;

class FinancialNeedODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $financialNeed;
    protected static $id = null;
    protected $totalAmount;
    
    protected $needByAmount;
    protected static $needByAmountId = null;
    
    protected $project;
    protected static $projectId = null;
    
    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanUp();
        
        $this->needByAmount = new FinancialNeedByAmount;
        $this->project = new Project;
        
        $this->financialNeed = new FinancialNeed;
        
        $this->totalAmount = new MultiCurrencyValue(10, 'EUR');
        $this->financialNeed->setTotalAmount($this->totalAmount);
        $this->financialNeed->addFinancialNeedByAmount($this->needByAmount);
        $this->financialNeed->setProject($this->project);        
        $this->financialNeed->validate();

        self::$dm->persist($this->financialNeed);
        self::$dm->persist($this->needByAmount);
        self::$dm->persist($this->project);
        self::$dm->flush();
        
        self::$id = $this->financialNeed->getId();
        self::$needByAmountId = $this->needByAmount->getId();
        self::$projectId = $this->project->getId();

        self::$dm->clear();
    }

    public function testFound() {
        $this->financialNeed = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\FinancialNeed', self::$id);
        $this->assertNotNull($this->financialNeed);
        $this->assertInstanceOf('Exposure\Model\FinancialNeed', $this->financialNeed);
    }
    
    public function testIsValid() {
        $this->financialNeed = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\FinancialNeed', self::$id);
        $this->financialNeed->validate();
    }
   
    public function testIsEqual() {
        $financialNeed = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\FinancialNeed', self::$id);
        $this->assertEquals($this->financialNeed->getTotalAmount()->getValueByCurrencyCode('EUR'), 
                $financialNeed->getTotalAmount()->getValueByCurrencyCode('EUR'));
        $needsByAmount = $financialNeed->getNeedsByAmount();
        $this->assertEquals(self::$needByAmountId, $needsByAmount[0]->getId());
        $this->assertEquals(self::$projectId, $financialNeed->getProject()->getId());
        $this->assertEquals(self::$id, $needsByAmount[0]->getContributedTotal()->getId());
        $this->assertEquals(self::$id, $financialNeed->getProject()->getFinancialNeed()->getId());
    }
   
    public function testRemove() {
        $this->financialNeed = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\FinancialNeed', self::$id);
        self::$dm->remove($this->financialNeed);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\FinancialNeed', self::$id));
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
            $financialNeed = 
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\FinancialNeed', self::$id);
            if(!is_null($financialNeed)) {
                self::$dm->remove($financialNeed);
            }
        }
        if (!is_null(self::$needByAmountId)) {
            $needByAmount = 
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\FinancialNeedByAmount', self::$needByAmountId);
            if(!is_null($needByAmount)) {
                self::$dm->remove($needByAmount);
            }
        }
        if (!is_null(self::$projectId)) {
            $project = 
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\Project', self::$projectId);
            if(!is_null($project)) {
                self::$dm->remove($project);
            }
        }
        self::$dm->flush();
    }
}

?>
