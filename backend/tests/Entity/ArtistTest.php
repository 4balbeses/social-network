<?php

namespace App\Tests\Entity;

use App\Entity\Artist;
use App\Entity\ArtistAlbum;
use PHPUnit\Framework\TestCase;

class ArtistTest extends TestCase
{
    public function testCreateArtist(): void
    {
        $artist = new Artist();
        
        $this->assertNull($artist->getId());
        $this->assertNull($artist->getName());
        $this->assertNull($artist->getDescription());
        $this->assertCount(0, $artist->getArtistAlbums());
    }

    public function testSetName(): void
    {
        $artist = new Artist();
        $name = 'John Doe';
        
        $result = $artist->setName($name);
        
        $this->assertSame($artist, $result);
        $this->assertSame($name, $artist->getName());
    }

    public function testSetDescription(): void
    {
        $artist = new Artist();
        $description = 'A talented musician';
        
        $result = $artist->setDescription($description);
        
        $this->assertSame($artist, $result);
        $this->assertSame($description, $artist->getDescription());
    }

    public function testSetNullDescription(): void
    {
        $artist = new Artist();
        
        $result = $artist->setDescription(null);
        
        $this->assertSame($artist, $result);
        $this->assertNull($artist->getDescription());
    }

    public function testAddArtistAlbum(): void
    {
        $artist = new Artist();
        $artistAlbum = new ArtistAlbum();
        
        $result = $artist->addArtistAlbum($artistAlbum);
        
        $this->assertSame($artist, $result);
        $this->assertTrue($artist->getArtistAlbums()->contains($artistAlbum));
        $this->assertSame($artist, $artistAlbum->getArtist());
    }

    public function testRemoveArtistAlbum(): void
    {
        $artist = new Artist();
        $artistAlbum = new ArtistAlbum();
        
        $artist->addArtistAlbum($artistAlbum);
        $result = $artist->removeArtistAlbum($artistAlbum);
        
        $this->assertSame($artist, $result);
        $this->assertFalse($artist->getArtistAlbums()->contains($artistAlbum));
    }
}