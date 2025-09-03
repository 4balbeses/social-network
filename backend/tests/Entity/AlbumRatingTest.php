<?php

namespace App\Tests\Entity;

use App\Entity\AlbumRating;
use App\Entity\User;
use App\Entity\Album;
use PHPUnit\Framework\TestCase;

class AlbumRatingTest extends TestCase
{
    public function testCreateAlbumRating(): void
    {
        $albumRating = new AlbumRating();
        
        $this->assertNull($albumRating->getId());
        $this->assertNull($albumRating->getRating());
        $this->assertNull($albumRating->getRatingUser());
        $this->assertNull($albumRating->getRatedAlbum());
        $this->assertInstanceOf(\DateTimeInterface::class, $albumRating->getRatedAt());
    }

    public function testSetRating(): void
    {
        $albumRating = new AlbumRating();
        $rating = 4;
        
        $result = $albumRating->setRating($rating);
        
        $this->assertSame($albumRating, $result);
        $this->assertSame($rating, $albumRating->getRating());
    }

    public function testSetRatingUser(): void
    {
        $albumRating = new AlbumRating();
        $user = new User();
        
        $result = $albumRating->setRatingUser($user);
        
        $this->assertSame($albumRating, $result);
        $this->assertSame($user, $albumRating->getRatingUser());
    }

    public function testSetRatedAlbum(): void
    {
        $albumRating = new AlbumRating();
        $album = new Album();
        
        $result = $albumRating->setRatedAlbum($album);
        
        $this->assertSame($albumRating, $result);
        $this->assertSame($album, $albumRating->getRatedAlbum());
    }

    public function testSetRatedAt(): void
    {
        $albumRating = new AlbumRating();
        $date = new \DateTime('2024-01-01 12:00:00');
        
        $result = $albumRating->setRatedAt($date);
        
        $this->assertSame($albumRating, $result);
        $this->assertSame($date, $albumRating->getRatedAt());
    }
}