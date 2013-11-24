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

use Exposure\Model\SubscriptionType,
    Exposure\Model\SubscriptionPrice,
    Exposure\Model\ViewRights,
    Exposure\Model\ProjectRights,
    Sociable\Model\MultiLanguageString,
    Sociable\ODM\ObjectDocumentMapper;

class SubscriptionTypeODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $subscriptionType;
    protected static $id = null;    
    const TYPE_LABEL = 'ZZZZZZ_type_label';

    protected $name;
    const NAME_STRING = 'name';
    const NAME_LANGUAGE = 'fr';

    protected $subscriptionPrice;
    const DURATION_IN_MONTHS = 6;
    
    protected $viewRights;
    protected $projectRights;
    
    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanUp();
        
        $this->subscriptionType = new SubscriptionType;
        $this->subscriptionType->setLabel(self::TYPE_LABEL);

        $this->name = new MultiLanguageString(self::NAME_STRING,
                self::NAME_LANGUAGE);
        $this->subscriptionType->setName($this->name);
        
        $this->subscriptionPrice = new SubscriptionPrice;
        $this->subscriptionPrice->setDurationInMonths(self::DURATION_IN_MONTHS);
        $this->subscriptionType->addSubscriptionPrice($this->subscriptionPrice);
        
        $this->projectRights = new ProjectRights;
        $this->subscriptionType->setProjectRights($this->projectRights);
        $this->viewRights = new ViewRights;
        $this->subscriptionType->setViewRights($this->viewRights);
        
        $this->subscriptionType->validate();

        self::$dm->persist($this->subscriptionType);
        self::$dm->flush();
        
        self::$id = $this->subscriptionType->getId();

        self::$dm->clear();
    }

    public function testFound() {
        $this->subscriptionType = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SubscriptionType', self::$id);
        $this->assertNotNull($this->subscriptionType);
        $this->assertInstanceOf('Exposure\Model\SubscriptionType', $this->subscriptionType);
    }
    
    public function testIsValid() {
        $this->subscriptionType = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SubscriptionType', self::$id);
        $this->subscriptionType->validate();
    }
   
    public function testIsEqual() {
        $subscriptionType = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SubscriptionType', self::$id);
        $this->assertEquals(self::NAME_STRING, $subscriptionType
                ->getName()->getStringByLanguageCode(self::NAME_LANGUAGE));
        $this->assertEquals(self::TYPE_LABEL, $subscriptionType->getLabel());
        $this->assertEquals(self::DURATION_IN_MONTHS, 
                $subscriptionType->getSubscriptionPrices()[0]->getDurationInMonths());
        $this->assertEquals($this->viewRights, $subscriptionType->getViewRights());
        $this->assertEquals($this->projectRights, $subscriptionType->getProjectRights());
    }
   
    public function testRemove() {
        $this->subscriptionType = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SubscriptionType', self::$id);
        self::$dm->remove($this->subscriptionType);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SubscriptionType', self::$id));
    }
    
    public function tearDown() {
        self::cleanUp();
    }
    
    public static function tearDownAfterClass() {
        self::cleanUp();
    }

    public static function cleanUp() {
        self::$dm->clear();
        $subscriptionType = ObjectDocumentMapper::getByLabel(self::$dm, 'Exposure\Model\SubscriptionType', self::TYPE_LABEL);
        if(!is_null($subscriptionType)) {
            self::$dm->remove($subscriptionType);
        }
        self::$dm->flush();
    }
}

?>
