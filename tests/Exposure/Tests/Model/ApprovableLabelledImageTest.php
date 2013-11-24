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

use Exposure\Model\ApprovableLabelledImage,
    Exposure\Model\ApprovableContent,
    Exposure\Model\ModerationStatus,
    Sociable\Model\MultiLanguageString,
    Sociable\Model\LabelledImage;

class ApprovableLabelledImageTest extends \PHPUnit_Framework_TestCase {

    protected $approvableLabelledImage;
    protected $moderationStatus;
    protected $current;
    protected $previous;

    public function setUp() {
        $this->approvableLabelledImage = new ApprovableLabelledImage();
        
        $this->moderationStatus = new ModerationStatus();
        $this->moderationStatus->setReasonCode('code');
        $this->moderationStatus->setStatus(ModerationStatus::STATUS_APPROVED);

        $this->current = new LabelledImage();
        $this->current->setImageFile(__DIR__ . '/current.png');
        $this->current->setMime(LabelledImage::MIME_PNG);
        $this->current->setDescription(new MultiLanguageString('chaîne de caractères', 'fr'));
        
        $this->previous = new LabelledImage();
        $this->previous->setImageFile(__DIR__ . '/previous.png');
        $this->previous->setMime(LabelledImage::MIME_PNG);
        $this->previous->setDescription(new MultiLanguageString('chaîne de caractères', 'fr'));
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\ApprovableContent', 
                $this->approvableLabelledImage);
        $this->assertEquals(ApprovableContent::TYPE_LABELLED_IMAGE, 
                $this->approvableLabelledImage->getType());
    }
    
    public function testSetGetCurrent() {
        $this->assertEquals($this->current, 
                $this->approvableLabelledImage->setCurrent($this->current));
        $this->assertEquals($this->current, 
                $this->approvableLabelledImage->getCurrent());
    }

    public function testSetGetPrevious() {
        $this->assertNull($this->approvableLabelledImage->setPrevious(null));
        $this->assertNull($this->approvableLabelledImage->getPrevious());
        $this->assertEquals($this->previous, 
                $this->approvableLabelledImage->setPrevious($this->previous));
        $this->assertEquals($this->previous, 
                $this->approvableLabelledImage->getPrevious());
    }
    
    public function testValidate_missingmoderationStatus() {
        $this->approvableLabelledImage->setCurrent($this->current);
        $this->setExpectedException('Exposure\Model\ApprovableContentException', 
            ApprovableContent::EXCEPTION_MISSING_STATUS);
        $this->approvableLabelledImage->validate();
    }

    public function testValidate_missingcurrent() {
        $this->approvableLabelledImage->setModerationStatus($this->moderationStatus);
        $this->setExpectedException('Exposure\Model\ApprovableContentException', 
            ApprovableContent::EXCEPTION_MISSING_CURRENT);
        $this->approvableLabelledImage->validate();
    }
    
    public function testValidate() {
        $this->approvableLabelledImage->setModerationStatus($this->moderationStatus);
        $this->approvableLabelledImage->setCurrent($this->current);
        $this->approvableLabelledImage->validate();
        $this->approvableLabelledImage->setPrevious($this->previous);
        $this->approvableLabelledImage->validate();
    }
    
    public function tearDown() {
        unset($this->approvableLabelledImage);
    }

}

?>
