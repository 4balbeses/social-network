<?php

namespace App\Tests\Entity;

use App\Entity\Genre;
use App\Entity\Track;
use PHPUnit\Framework\TestCase;

class GenreTest extends TestCase
{
    public function testCreateGenre(): void
    {
        $genre = new Genre();
        
        $this->assertNull($genre->getId());
        $this->assertNull($genre->getName());
        $this->assertNull($genre->getDescription());
        $this->assertCount(0, $genre->getTracks());
    }

    public function testSetName(): void
    {
        $genre = new Genre();
        $name = 'Rock';
        
        $result = $genre->setName($name);
        
        $this->assertSame($genre, $result);
        $this->assertSame($name, $genre->getName());
    }

    public function testSetDescription(): void
    {
        $genre = new Genre();
        $description = 'Heavy guitar music';
        
        $result = $genre->setDescription($description);
        
        $this->assertSame($genre, $result);
        $this->assertSame($description, $genre->getDescription());
    }

    public function testSetNullDescription(): void
    {
        $genre = new Genre();
        
        $result = $genre->setDescription(null);
        
        $this->assertSame($genre, $result);
        $this->assertNull($genre->getDescription());
    }

    public function testAddTrack(): void
    {
        $genre = new Genre();
        $track = new Track();
        
        $result = $genre->addTrack($track);
        
        $this->assertSame($genre, $result);
        $this->assertTrue($genre->getTracks()->contains($track));
        $this->assertSame($genre, $track->getGenre());
    }

    public function testAddSameTrackTwice(): void
    {
        $genre = new Genre();
        $track = new Track();
        
        $genre->addTrack($track);
        $genre->addTrack($track);
        
        $this->assertCount(1, $genre->getTracks());
    }

    public function testRemoveTrack(): void
    {
        $genre = new Genre();
        $track = new Track();
        
        $genre->addTrack($track);
        $result = $genre->removeTrack($track);
        
        $this->assertSame($genre, $result);
        $this->assertFalse($genre->getTracks()->contains($track));
        $this->assertNull($track->getGenre());
    }

    public function testRemoveNonExistentTrack(): void
    {
        $genre = new Genre();
        $track = new Track();
        
        $result = $genre->removeTrack($track);
        
        $this->assertSame($genre, $result);
        $this->assertCount(0, $genre->getTracks());
    }
}