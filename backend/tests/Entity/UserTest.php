<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Playlist;
use App\Entity\Tag;
use App\Entity\UserPlaylist;
use App\Entity\PlaylistRating;
use App\Entity\AlbumRating;
use App\Entity\TrackRating;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testCreateUser(): void
    {
        $user = new User();
        
        $this->assertNull($user->getId());
        $this->assertNull($user->getUsername());
        $this->assertNull($user->getPassword());
        $this->assertNull($user->getRegisteredAt());
        $this->assertNull($user->getFullName());
        $this->assertSame(['ROLE_USER'], $user->getRoles());
        $this->assertCount(0, $user->getPlaylists());
        $this->assertCount(0, $user->getTags());
        $this->assertCount(0, $user->getUserPlaylists());
        $this->assertCount(0, $user->getPlaylistRatings());
        $this->assertCount(0, $user->getAlbumRatings());
        $this->assertCount(0, $user->getTrackRatings());
    }

    public function testSetUsername(): void
    {
        $user = new User();
        $username = 'testuser';
        
        $result = $user->setUsername($username);
        
        $this->assertSame($user, $result);
        $this->assertSame($username, $user->getUsername());
        $this->assertSame($username, $user->getUserIdentifier());
    }

    public function testSetPassword(): void
    {
        $user = new User();
        $password = 'hashedpassword';
        
        $result = $user->setPassword($password);
        
        $this->assertSame($user, $result);
        $this->assertSame($password, $user->getPassword());
    }

    public function testSetRegisteredAt(): void
    {
        $user = new User();
        $date = new \DateTime('2024-01-01 12:00:00');
        
        $result = $user->setRegisteredAt($date);
        
        $this->assertSame($user, $result);
        $this->assertSame($date, $user->getRegisteredAt());
    }

    public function testSetFullName(): void
    {
        $user = new User();
        $fullName = 'John Doe';
        
        $result = $user->setFullName($fullName);
        
        $this->assertSame($user, $result);
        $this->assertSame($fullName, $user->getFullName());
    }

    public function testSetRoles(): void
    {
        $user = new User();
        $roles = ['ROLE_USER', 'ROLE_ADMIN'];
        
        $result = $user->setRoles($roles);
        
        $this->assertSame($user, $result);
        $this->assertSame($roles, $user->getRoles());
    }

    public function testSetRolesAutomaticallyAddsUserRole(): void
    {
        $user = new User();
        $roles = ['ROLE_ADMIN'];
        
        $user->setRoles($roles);
        
        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }

    public function testAddPlaylist(): void
    {
        $user = new User();
        $playlist = new Playlist();
        
        $result = $user->addPlaylist($playlist);
        
        $this->assertSame($user, $result);
        $this->assertTrue($user->getPlaylists()->contains($playlist));
        $this->assertSame($user, $playlist->getOwner());
    }

    public function testAddSamePlaylistTwice(): void
    {
        $user = new User();
        $playlist = new Playlist();
        
        $user->addPlaylist($playlist);
        $user->addPlaylist($playlist);
        
        $this->assertCount(1, $user->getPlaylists());
    }

    public function testRemovePlaylist(): void
    {
        $user = new User();
        $playlist = new Playlist();
        
        $user->addPlaylist($playlist);
        $result = $user->removePlaylist($playlist);
        
        $this->assertSame($user, $result);
        $this->assertFalse($user->getPlaylists()->contains($playlist));
    }

    public function testAddTag(): void
    {
        $user = new User();
        $tag = new Tag();
        
        $result = $user->addTag($tag);
        
        $this->assertSame($user, $result);
        $this->assertTrue($user->getTags()->contains($tag));
        $this->assertSame($user, $tag->getAuthor());
    }

    public function testRemoveTag(): void
    {
        $user = new User();
        $tag = new Tag();
        
        $user->addTag($tag);
        $result = $user->removeTag($tag);
        
        $this->assertSame($user, $result);
        $this->assertFalse($user->getTags()->contains($tag));
    }

    public function testAddUserPlaylist(): void
    {
        $user = new User();
        $userPlaylist = new UserPlaylist();
        
        $result = $user->addUserPlaylist($userPlaylist);
        
        $this->assertSame($user, $result);
        $this->assertTrue($user->getUserPlaylists()->contains($userPlaylist));
        $this->assertSame($user, $userPlaylist->getUser());
    }

    public function testRemoveUserPlaylist(): void
    {
        $user = new User();
        $userPlaylist = new UserPlaylist();
        
        $user->addUserPlaylist($userPlaylist);
        $result = $user->removeUserPlaylist($userPlaylist);
        
        $this->assertSame($user, $result);
        $this->assertFalse($user->getUserPlaylists()->contains($userPlaylist));
    }

    public function testAddPlaylistRating(): void
    {
        $user = new User();
        $playlistRating = new PlaylistRating();
        
        $result = $user->addPlaylistRating($playlistRating);
        
        $this->assertSame($user, $result);
        $this->assertTrue($user->getPlaylistRatings()->contains($playlistRating));
        $this->assertSame($user, $playlistRating->getRatingUser());
    }

    public function testRemovePlaylistRating(): void
    {
        $user = new User();
        $playlistRating = new PlaylistRating();
        
        $user->addPlaylistRating($playlistRating);
        $result = $user->removePlaylistRating($playlistRating);
        
        $this->assertSame($user, $result);
        $this->assertFalse($user->getPlaylistRatings()->contains($playlistRating));
    }

    public function testAddAlbumRating(): void
    {
        $user = new User();
        $albumRating = new AlbumRating();
        
        $result = $user->addAlbumRating($albumRating);
        
        $this->assertSame($user, $result);
        $this->assertTrue($user->getAlbumRatings()->contains($albumRating));
        $this->assertSame($user, $albumRating->getRatingUser());
    }

    public function testRemoveAlbumRating(): void
    {
        $user = new User();
        $albumRating = new AlbumRating();
        
        $user->addAlbumRating($albumRating);
        $result = $user->removeAlbumRating($albumRating);
        
        $this->assertSame($user, $result);
        $this->assertFalse($user->getAlbumRatings()->contains($albumRating));
    }

    public function testAddTrackRating(): void
    {
        $user = new User();
        $trackRating = new TrackRating();
        
        $result = $user->addTrackRating($trackRating);
        
        $this->assertSame($user, $result);
        $this->assertTrue($user->getTrackRatings()->contains($trackRating));
        $this->assertSame($user, $trackRating->getRatingUser());
    }

    public function testRemoveTrackRating(): void
    {
        $user = new User();
        $trackRating = new TrackRating();
        
        $user->addTrackRating($trackRating);
        $result = $user->removeTrackRating($trackRating);
        
        $this->assertSame($user, $result);
        $this->assertFalse($user->getTrackRatings()->contains($trackRating));
    }

    public function testEraseCredentials(): void
    {
        $user = new User();
        
        // This method should exist but do nothing in this implementation
        $this->assertNull($user->eraseCredentials());
    }
}