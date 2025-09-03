<?php

namespace App\Tests\Entity;

use App\Entity\Tag;
use App\Entity\User;
use App\Entity\TrackTag;
use PHPUnit\Framework\TestCase;

class TagTest extends TestCase
{
    public function testCreateTag(): void
    {
        $tag = new Tag();
        
        $this->assertNull($tag->getId());
        $this->assertNull($tag->getName());
        $this->assertNull($tag->getDescription());
        $this->assertNull($tag->getAuthor());
        $this->assertInstanceOf(\DateTimeInterface::class, $tag->getCreatedAt());
        $this->assertCount(0, $tag->getTrackTags());
    }

    public function testSetName(): void
    {
        $tag = new Tag();
        $name = 'uplifting';
        
        $result = $tag->setName($name);
        
        $this->assertSame($tag, $result);
        $this->assertSame($name, $tag->getName());
    }

    public function testSetDescription(): void
    {
        $tag = new Tag();
        $description = 'Music that makes you feel good';
        
        $result = $tag->setDescription($description);
        
        $this->assertSame($tag, $result);
        $this->assertSame($description, $tag->getDescription());
    }

    public function testSetAuthor(): void
    {
        $tag = new Tag();
        $user = new User();
        
        $result = $tag->setAuthor($user);
        
        $this->assertSame($tag, $result);
        $this->assertSame($user, $tag->getAuthor());
    }

    public function testSetCreatedAt(): void
    {
        $tag = new Tag();
        $date = new \DateTime('2024-01-01 12:00:00');
        
        $result = $tag->setCreatedAt($date);
        
        $this->assertSame($tag, $result);
        $this->assertSame($date, $tag->getCreatedAt());
    }

    public function testAddTrackTag(): void
    {
        $tag = new Tag();
        $trackTag = new TrackTag();
        
        $result = $tag->addTrackTag($trackTag);
        
        $this->assertSame($tag, $result);
        $this->assertTrue($tag->getTrackTags()->contains($trackTag));
        $this->assertSame($tag, $trackTag->getTag());
    }

    public function testRemoveTrackTag(): void
    {
        $tag = new Tag();
        $trackTag = new TrackTag();
        
        $tag->addTrackTag($trackTag);
        $result = $tag->removeTrackTag($trackTag);
        
        $this->assertSame($tag, $result);
        $this->assertFalse($tag->getTrackTags()->contains($trackTag));
    }
}