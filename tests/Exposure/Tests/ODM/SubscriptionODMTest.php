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

use Exposure\Model\Subscription,
    Exposure\Model\SubscriptionTypeAndDuration,
    Sociable\Model\ConfirmationCode,
    Sociable\Model\MultiLanguageString,
    Sociable\ODM\ObjectDocumentMapper;

class SubscriptionODMTest extends \PHPUnit_Framework_TestCase {
    protected static $dm ;
    
    protected $subscription;
    protected static $id = null;    
    
    const STATUS = Subscription::STATUS_ACTIVE;
    protected $paymentConfirmationCode;
    protected $startDateTime;
    protected $endDateTime;
    
    protected $typeAndDuration;
    protected static $typeAndDurationId = null;
    
    public static function setUpBeforeClass() {
        include EXPOSURE_ROOT.'/sys/config/config-test.inc.php' ; // initialises $config
        self::$dm = $config->getDocumentManager();
    }
    
    public function setUp() {
        self::cleanUp();

        $this->subscription = new Subscription;
        $this->subscription->setStatus(self::STATUS);
        
        $this->paymentConfirmationCode = new ConfirmationCode;
        $this->subscription->setPaymentConfirmationCode($this->paymentConfirmationCode);
        
        $this->startDateTime = new \DateTime;
        $this->subscription->setStartDateTime($this->startDateTime);
        
        $this->endDateTime = new \DateTime;
        $this->subscription->setEndDateTime($this->endDateTime);
        
        $this->typeAndDuration = new SubscriptionTypeAndDuration;
        $this->subscription->setTypeAndDuration($this->typeAndDuration);
        
        $this->subscription->validate();

        self::$dm->persist($this->subscription);
        self::$dm->persist($this->typeAndDuration);
        self::$dm->flush();
        
        self::$id = $this->subscription->getId();
        self::$typeAndDurationId = $this->typeAndDuration->getId();

        self::$dm->clear();
    }

    public function testFound() {
        $this->subscription = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\Subscription', self::$id);
        $this->assertNotNull($this->subscription);
        $this->assertInstanceOf('Exposure\Model\Subscription', $this->subscription);
    }
    
    public function testIsValid() {
        $this->subscription = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\Subscription', self::$id);
        $this->subscription->validate();
    }
   
    public function testIsEqual() {
        $subscription = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\Subscription', self::$id);
        $this->assertEquals(self::STATUS, $subscription->getStatus());
        $this->assertEquals($this->paymentConfirmationCode, $subscription->getPaymentConfirmationCode());
        $this->assertEquals($this->startDateTime, $subscription->getStartDateTime());
        $this->assertEquals($this->endDateTime, $subscription->getEndDateTime());
        $this->assertEquals(self::$typeAndDurationId, $subscription->getTypeAndDuration()->getId());
    }
   
    public function testRemove() {
        $this->subscription = 
                ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\Subscription', self::$id);
        self::$dm->remove($this->subscription);
        self::$dm->flush();

        $this->assertNull(ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\Subscription', self::$id));
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
            $subscription = 
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\Subscription', self::$id);
            if(!is_null($subscription)) {
                self::$dm->remove($subscription);
            }
        }
        if (!is_null(self::$typeAndDurationId)) {
            $typeAndDuration = 
                    ObjectDocumentMapper::getById(self::$dm, 'Exposure\Model\SubscriptionTypeAndDuration', self::$typeAndDurationId);
            if(!is_null($typeAndDuration)) {
                self::$dm->remove($typeAndDuration);
            }
        }
        self::$dm->flush();
    }
}

?>
