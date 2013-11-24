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

use Exposure\Model\CommentOnProject,
    Exposure\Model\Comment,
    Exposure\Model\User,
    Exposure\Model\Project;
    
class CommentOnProjectTest extends \PHPUnit_Framework_TestCase {

    protected $comment;
    const STATUS = Comment::STATUS_PUBLISHED;
    protected $dateTime;
    const CONTENT = 'content';
    const RATING = 4;
    protected $from;
    protected $project;

    public function setUp() {
        $this->comment = new CommentOnProject();
        
        $this->dateTime = new \DateTime();
        $this->from = new User();
        $this->project = new Project();
        
    }

    public function test__construct() {
        $this->assertInstanceOf('Exposure\Model\CommentOnProject', $this->comment);
        $this->assertEquals(Comment::TYPE_COMMENT_ON_PROJECT, $this->comment->getType());
    }
    
    public function testSetGetProject() {
        $this->assertEquals($this->project, $this->comment->setProject($this->project));
        $this->assertEquals($this->project, $this->comment->getProject());
    }
        
    public function testValidate() {
        $this->comment->setStatus(self::STATUS);
        $this->comment->setDateTime($this->dateTime);
        $this->comment->setContent(self::CONTENT);
        $this->comment->setFrom($this->from);
        $this->comment->setProject($this->project);
        $this->comment->validate();
        $this->comment->setRating(self::RATING);
        $this->comment->validate();
    }

    public function tearDown() {
        unset($this->comment);
    }

}

?>
