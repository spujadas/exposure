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

use Exposure\Model\FinancialNeedByAmount,
    Exposure\Model\SponsorContribution,
    Exposure\Model\Project,
    Exposure\Model\SponsorReturn,
    Sociable\ODM\ObjectDocumentMapper,
    Sociable\Model\MultiLanguageString,
    Sociable\Model\MultiCurrencyValue;

class FinancialNeedByAmountODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $financialNeedByAmount;
    protected static $id = null;
    protected $description;
    protected $amount;
    
    protected $contribution;
    protected static $contributionId = null;
    
    protected $return;
    protected static $returnId = null;
    
    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanUp();
        
        $this->contribution = new SponsorContribution;
        $this->return = new SponsorReturn;
        $this->project = new Project;
        
        $this->financialNeedByAmount = new FinancialNeedByAmount;
        
        $this->description = new MultiLanguageString('description', 'fr');
        $this->financialNeedByAmount->setDescription($this->description);
        $this->amount = new MultiCurrencyValue(10, 'EUR');
        $this->financialNeedByAmount->setAmount($this->amount);
        $this->financialNeedByAmount->setContribution($this->contribution);
        $this->financialNeedByAmount->setReturn($this->return);
        $this->financialNeedByAmount->validate();

        self::$dm->persist($this->financialNeedByAmount);
        self::$dm->persist($this->contribution);
        self::$dm->persist($this->return);
        self::$dm->flush();
        
        self::$id = $this->financialNeedByAmount->getId();
        self::$contributionId = $this->contribution->getId();
        self::$returnId = $this->return->getId();

        self::$dm->clear();
    }

    public function testFound() {
        $this->financialNeedByAmount = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\FinancialNeedByAmount', self::$id);
        $this->assertNotNull($this->financialNeedByAmount);
        $this->assertInstanceOf('Exposure\Model\FinancialNeedByAmount', $this->financialNeedByAmount);
    }
    
    public function testIsValid() {
        $this->financialNeedByAmount = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\FinancialNeedByAmount', self::$id);
        $this->financialNeedByAmount->validate();
    }
   
    public function testIsEqual() {
        $financialNeedByAmount = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\FinancialNeedByAmount', self::$id);
        $this->assertEquals($this->financialNeedByAmount->getAmount()->getValueByCurrencyCode('EUR'), 
                $financialNeedByAmount->getAmount()->getValueByCurrencyCode('EUR'));
        $this->assertEquals(self::$contributionId, $financialNeedByAmount->getContribution()->getId());
        $this->assertEquals(self::$returnId, $financialNeedByAmount->getReturn()->getId());
        $this->assertEquals(self::$id, $financialNeedByAmount->getContribution()
                ->getContributedFinancialNeedByAmount()->getId());
        $this->assertEquals(self::$id, $financialNeedByAmount->getReturn()
                ->getReturnedFinancialNeedByAmount()->getId());
    }
   
    public function testRemove() {
        $this->financialNeedByAmount = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\FinancialNeedByAmount', self::$id);
        self::$dm->remove($this->financialNeedByAmount);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\FinancialNeedByAmount', self::$id));
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
            $financialNeedByAmount = 
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\FinancialNeedByAmount', self::$id);
            if(!is_null($financialNeedByAmount)) {
                self::$dm->remove($financialNeedByAmount);
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
        self::$dm->flush();
    }
}

?>
