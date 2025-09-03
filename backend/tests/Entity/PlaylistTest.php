<?php

namespace App\Tests\Entity;

use App\Entity\Playlist;
use App\Entity\User;
use App\Entity\TrackPlaylist;
use App\Entity\UserPlaylist;
use App\Entity\PlaylistRating;
use PHPUnit\Framework\TestCase;

class PlaylistTest extends TestCase
{
    public function testCreatePlaylist(): void
    {
        $playlist = new Playlist();
        
        $this->assertNull($playlist->getId());
        $this->assertNull($playlist->getName());
        $this->assertNull($playlist->getDescription());
        $this->assertNull($playlist->getOwner());
        $this->assertInstanceOf(\DateTimeInterface::class, $playlist->getCreatedAt());
        $this->assertCount(0, $playlist->getTrackPlaylists());
        $this->assertCount(0, $playlist->getUserPlaylists());
        $this->assertCount(0, $playlist->getPlaylistRatings());
    }

    public function testSetName(): void
    {
        $playlist = new Playlist();
        $name = 'My Favorites';
        
        $result = $playlist->setName($name);
        
        $this->assertSame($playlist, $result);
        $this->assertSame($name, $playlist->getName());
    }

    public function testSetDescription(): void
    {
        $playlist = new Playlist();
        $description = 'Songs I love to listen to';
        
        $result = $playlist->setDescription($description);
        
        $this->assertSame($playlist, $result);
        $this->assertSame($description, $playlist->getDescription());
    }

    public function testSetOwner(): void
    {
        $playlist = new Playlist();
        $user = new User();
        
        $result = $playlist->setOwner($user);
        
        $this->assertSame($playlist, $result);
        $this->assertSame($user, $playlist->getOwner());
    }

    public function testSetCreatedAt(): void
    {
        $playlist = new Playlist();
        $date = new \DateTime('2024-01-01 12:00:00');
        
        $result = $playlist->setCreatedAt($date);
        
        $this->assertSame($playlist, $result);
        $this->assertSame($date, $playlist->getCreatedAt());
    }

    public function testAddTrackPlaylist(): void
    {
        $playlist = new Playlist();
        $trackPlaylist = new TrackPlaylist();
        
        $result = $playlist->addTrackPlaylist($trackPlaylist);
        
        $this->assertSame($playlist, $result);
        $this->assertTrue($playlist->getTrackPlaylists()->contains($trackPlaylist));
        $this->assertSame($playlist, $trackPlaylist->getPlaylist());
    }

    public function testRemoveTrackPlaylist(): void
    {
        $playlist = new Playlist();
        $trackPlaylist = new TrackPlaylist();
        
        $playlist->addTrackPlaylist($trackPlaylist);
        $result = $playlist->removeTrackPlaylist($trackPlaylist);
        
        $this->assertSame($playlist, $result);
        $this->assertFalse($playlist->getTrackPlaylists()->contains($trackPlaylist));
    }

    public function testAddUserPlaylist(): void
    {
        $playlist = new Playlist();
        $userPlaylist = new UserPlaylist();
        
        $result = $playlist->addUserPlaylist($userPlaylist);
        
        $this->assertSame($playlist, $result);
        $this->assertTrue($playlist->getUserPlaylists()->contains($userPlaylist));
        $this->assertSame($playlist, $userPlaylist->getPlaylist());
    }

    public function testRemoveUserPlaylist(): void
    {
        $playlist = new Playlist();
        $userPlaylist = new UserPlaylist();
        
        $playlist->addUserPlaylist($userPlaylist);
        $result = $playlist->removeUserPlaylist($userPlaylist);
        
        $this->assertSame($playlist, $result);
        $this->assertFalse($playlist->getUserPlaylists()->contains($userPlaylist));
    }

    public function testAddPlaylistRating(): void
    {
        $playlist = new Playlist();
        $playlistRating = new PlaylistRating();
        
        $result = $playlist->addPlaylistRating($playlistRating);
        
        $this->assertSame($playlist, $result);
        $this->assertTrue($playlist->getPlaylistRatings()->contains($playlistRating));
        $this->assertSame($playlist, $playlistRating->getRatedPlaylist());
    }

    public function testRemovePlaylistRating(): void
    {
        $playlist = new Playlist();
        $playlistRating = new PlaylistRating();
        
        $playlist->addPlaylistRating($playlistRating);
        $result = $playlist->removePlaylistRating($playlistRating);
        
        $this->assertSame($playlist, $result);
        $this->assertFalse($playlist->getPlaylistRatings()->contains($playlistRating));
    }
}