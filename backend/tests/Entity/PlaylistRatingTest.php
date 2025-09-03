<?php

namespace App\Tests\Entity;

use App\Entity\PlaylistRating;
use App\Entity\User;
use App\Entity\Playlist;
use PHPUnit\Framework\TestCase;

class PlaylistRatingTest extends TestCase
{
    public function testCreatePlaylistRating(): void
    {
        $playlistRating = new PlaylistRating();
        
        $this->assertNull($playlistRating->getId());
        $this->assertNull($playlistRating->getRating());
        $this->assertNull($playlistRating->getRatingUser());
        $this->assertNull($playlistRating->getRatedPlaylist());
        $this->assertInstanceOf(\DateTimeInterface::class, $playlistRating->getRatedAt());
    }

    public function testSetRating(): void
    {
        $playlistRating = new PlaylistRating();
        $rating = 3;
        
        $result = $playlistRating->setRating($rating);
        
        $this->assertSame($playlistRating, $result);
        $this->assertSame($rating, $playlistRating->getRating());
    }

    public function testSetRatingUser(): void
    {
        $playlistRating = new PlaylistRating();
        $user = new User();
        
        $result = $playlistRating->setRatingUser($user);
        
        $this->assertSame($playlistRating, $result);
        $this->assertSame($user, $playlistRating->getRatingUser());
    }

    public function testSetRatedPlaylist(): void
    {
        $playlistRating = new PlaylistRating();
        $playlist = new Playlist();
        
        $result = $playlistRating->setRatedPlaylist($playlist);
        
        $this->assertSame($playlistRating, $result);
        $this->assertSame($playlist, $playlistRating->getRatedPlaylist());
    }

    public function testSetRatedAt(): void
    {
        $playlistRating = new PlaylistRating();
        $date = new \DateTime('2024-01-01 12:00:00');
        
        $result = $playlistRating->setRatedAt($date);
        
        $this->assertSame($playlistRating, $result);
        $this->assertSame($date, $playlistRating->getRatedAt());
    }
}