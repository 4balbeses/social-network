<?php

namespace App\Tests\Entity;

use App\Entity\TrackRating;
use App\Entity\User;
use App\Entity\Track;
use PHPUnit\Framework\TestCase;

class TrackRatingTest extends TestCase
{
    public function testCreateTrackRating(): void
    {
        $trackRating = new TrackRating();
        
        $this->assertNull($trackRating->getId());
        $this->assertNull($trackRating->getRating());
        $this->assertNull($trackRating->getRatingUser());
        $this->assertNull($trackRating->getRatedTrack());
        $this->assertInstanceOf(\DateTimeInterface::class, $trackRating->getRatedAt());
    }

    public function testSetRating(): void
    {
        $trackRating = new TrackRating();
        $rating = 5;
        
        $result = $trackRating->setRating($rating);
        
        $this->assertSame($trackRating, $result);
        $this->assertSame($rating, $trackRating->getRating());
    }

    public function testSetRatingUser(): void
    {
        $trackRating = new TrackRating();
        $user = new User();
        
        $result = $trackRating->setRatingUser($user);
        
        $this->assertSame($trackRating, $result);
        $this->assertSame($user, $trackRating->getRatingUser());
    }

    public function testSetRatedTrack(): void
    {
        $trackRating = new TrackRating();
        $track = new Track();
        
        $result = $trackRating->setRatedTrack($track);
        
        $this->assertSame($trackRating, $result);
        $this->assertSame($track, $trackRating->getRatedTrack());
    }

    public function testSetRatedAt(): void
    {
        $trackRating = new TrackRating();
        $date = new \DateTime('2024-01-01 12:00:00');
        
        $result = $trackRating->setRatedAt($date);
        
        $this->assertSame($trackRating, $result);
        $this->assertSame($date, $trackRating->getRatedAt());
    }
}