<?php

namespace App\Tests\Entity;

use App\Entity\Album;
use App\Entity\ArtistAlbum;
use App\Entity\TrackAlbum;
use App\Entity\AlbumRating;
use PHPUnit\Framework\TestCase;

class AlbumTest extends TestCase
{
    public function testCreateAlbum(): void
    {
        $album = new Album();
        
        $this->assertNull($album->getId());
        $this->assertNull($album->getName());
        $this->assertNull($album->getDescription());
        $this->assertInstanceOf(\DateTimeInterface::class, $album->getCreatedAt());
        $this->assertCount(0, $album->getArtistAlbums());
        $this->assertCount(0, $album->getTrackAlbums());
        $this->assertCount(0, $album->getAlbumRatings());
    }

    public function testSetName(): void
    {
        $album = new Album();
        $name = 'My Album';
        
        $result = $album->setName($name);
        
        $this->assertSame($album, $result);
        $this->assertSame($name, $album->getName());
    }

    public function testSetDescription(): void
    {
        $album = new Album();
        $description = 'A collection of great songs';
        
        $result = $album->setDescription($description);
        
        $this->assertSame($album, $result);
        $this->assertSame($description, $album->getDescription());
    }

    public function testSetNullDescription(): void
    {
        $album = new Album();
        
        $result = $album->setDescription(null);
        
        $this->assertSame($album, $result);
        $this->assertNull($album->getDescription());
    }

    public function testSetCreatedAt(): void
    {
        $album = new Album();
        $date = new \DateTime('2024-01-01 12:00:00');
        
        $result = $album->setCreatedAt($date);
        
        $this->assertSame($album, $result);
        $this->assertSame($date, $album->getCreatedAt());
    }

    public function testAddArtistAlbum(): void
    {
        $album = new Album();
        $artistAlbum = new ArtistAlbum();
        
        $result = $album->addArtistAlbum($artistAlbum);
        
        $this->assertSame($album, $result);
        $this->assertTrue($album->getArtistAlbums()->contains($artistAlbum));
        $this->assertSame($album, $artistAlbum->getAlbum());
    }

    public function testAddSameArtistAlbumTwice(): void
    {
        $album = new Album();
        $artistAlbum = new ArtistAlbum();
        
        $album->addArtistAlbum($artistAlbum);
        $album->addArtistAlbum($artistAlbum);
        
        $this->assertCount(1, $album->getArtistAlbums());
    }

    public function testRemoveArtistAlbum(): void
    {
        $album = new Album();
        $artistAlbum = new ArtistAlbum();
        
        $album->addArtistAlbum($artistAlbum);
        $result = $album->removeArtistAlbum($artistAlbum);
        
        $this->assertSame($album, $result);
        $this->assertFalse($album->getArtistAlbums()->contains($artistAlbum));
    }

    public function testAddTrackAlbum(): void
    {
        $album = new Album();
        $trackAlbum = new TrackAlbum();
        
        $result = $album->addTrackAlbum($trackAlbum);
        
        $this->assertSame($album, $result);
        $this->assertTrue($album->getTrackAlbums()->contains($trackAlbum));
        $this->assertSame($album, $trackAlbum->getAlbum());
    }

    public function testRemoveTrackAlbum(): void
    {
        $album = new Album();
        $trackAlbum = new TrackAlbum();
        
        $album->addTrackAlbum($trackAlbum);
        $result = $album->removeTrackAlbum($trackAlbum);
        
        $this->assertSame($album, $result);
        $this->assertFalse($album->getTrackAlbums()->contains($trackAlbum));
    }

    public function testAddAlbumRating(): void
    {
        $album = new Album();
        $albumRating = new AlbumRating();
        
        $result = $album->addAlbumRating($albumRating);
        
        $this->assertSame($album, $result);
        $this->assertTrue($album->getAlbumRatings()->contains($albumRating));
        $this->assertSame($album, $albumRating->getRatedAlbum());
    }

    public function testRemoveAlbumRating(): void
    {
        $album = new Album();
        $albumRating = new AlbumRating();
        
        $album->addAlbumRating($albumRating);
        $result = $album->removeAlbumRating($albumRating);
        
        $this->assertSame($album, $result);
        $this->assertFalse($album->getAlbumRatings()->contains($albumRating));
    }
}