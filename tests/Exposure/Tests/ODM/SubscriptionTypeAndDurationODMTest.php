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

use Exposure\Model\SubscriptionTypeAndDuration,
    Exposure\Model\SubscriptionType,
    Exposure\Model\SubscriptionPrice,
    Sociable\ODM\ObjectDocumentMapper;

class SubscriptionTypeAndDurationODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $subscriptionTypeAndDuration;
    protected static $id = null;
    
    protected $type;
    const TYPE_LABEL = 'ZZZZZZ_type_label';
    protected $subscriptionPrice;
    
    const DURATION_IN_MONTHS = 6;
    
    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanUp();
        
        $this->subscriptionTypeAndDuration = new SubscriptionTypeAndDuration;
        
        $this->type = new SubscriptionType;
        $this->type->setLabel(self::TYPE_LABEL);
        
        $this->subscriptionPrice = new SubscriptionPrice;
        $this->subscriptionPrice->setDurationInMonths(self::DURATION_IN_MONTHS);
        $this->type->addSubscriptionPrice($this->subscriptionPrice);
        
        $this->subscriptionTypeAndDuration->setType($this->type);
        $this->subscriptionTypeAndDuration->setDurationInMonths(self::DURATION_IN_MONTHS);
        $this->subscriptionTypeAndDuration->validate();

        self::$dm->persist($this->subscriptionTypeAndDuration);
        self::$dm->persist($this->type);
        self::$dm->flush();
        
        self::$id = $this->subscriptionTypeAndDuration->getId();
    
        self::$dm->clear();
    }

    public function testFound() {
        $this->subscriptionTypeAndDuration = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SubscriptionTypeAndDuration', self::$id);
        $this->assertNotNull($this->subscriptionTypeAndDuration);
        $this->assertInstanceOf('Exposure\Model\SubscriptionTypeAndDuration', $this->subscriptionTypeAndDuration);
    }
    
    public function testIsValid() {
        $this->subscriptionTypeAndDuration = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SubscriptionTypeAndDuration', self::$id);
        $this->subscriptionTypeAndDuration->validate();
    }
   
    public function testIsEqual() {
        $subscriptionTypeAndDuration = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SubscriptionTypeAndDuration', self::$id);
        $this->assertEquals(self::DURATION_IN_MONTHS, 
                $subscriptionTypeAndDuration->getDurationInMonths());
        $this->assertEquals(self::TYPE_LABEL, 
                $subscriptionTypeAndDuration->getType()->getLabel());
    }
   
    public function testRemove() {
        $this->subscriptionTypeAndDuration = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SubscriptionTypeAndDuration', self::$id);
        self::$dm->remove($this->subscriptionTypeAndDuration);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SubscriptionTypeAndDuration', self::$id));
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
            $subscriptionTypeAndDuration = 
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SubscriptionTypeAndDuration', self::$id);
            if(!is_null($subscriptionTypeAndDuration)) {
                self::$dm->remove($subscriptionTypeAndDuration);
            }
        }
        $type = ObjectDocumentMapper::getByLabel(self::$dm, 'Exposure\Model\SubscriptionType', self::TYPE_LABEL);
        if(!is_null($type)) {
            self::$dm->remove($type);
        }
        self::$dm->flush();
    }
}

?>
