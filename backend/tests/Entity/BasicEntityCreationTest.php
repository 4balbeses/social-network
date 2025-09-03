<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;

class BasicEntityCreationTest extends TestCase
{
    public function testGenreBasicCreation(): void
    {
        $genre = new \App\Entity\Genre();
        $this->assertInstanceOf(\App\Entity\Genre::class, $genre);
        $this->assertNull($genre->getName());
        
        $genre->setName('Rock');
        $this->assertSame('Rock', $genre->getName());
        
        $genre->setDescription('Heavy music');
        $this->assertSame('Heavy music', $genre->getDescription());
    }

    public function testArtistBasicCreation(): void
    {
        $artist = new \App\Entity\Artist();
        $this->assertInstanceOf(\App\Entity\Artist::class, $artist);
        $this->assertNull($artist->getFullName());
        
        $artist->setFullName('John Doe');
        $this->assertSame('John Doe', $artist->getFullName());
    }

    public function testAlbumBasicCreation(): void
    {
        $album = new \App\Entity\Album();
        $this->assertInstanceOf(\App\Entity\Album::class, $album);
        $this->assertNull($album->getName());
        $this->assertInstanceOf(\DateTimeInterface::class, $album->getCreatedAt());
        
        $album->setName('My Album');
        $this->assertSame('My Album', $album->getName());
    }

    public function testTrackBasicCreation(): void
    {
        $track = new \App\Entity\Track();
        $this->assertInstanceOf(\App\Entity\Track::class, $track);
        $this->assertNull($track->getName());
        
        $track->setName('My Song');
        $this->assertSame('My Song', $track->getName());
    }

    public function testPlaylistBasicCreation(): void
    {
        $playlist = new \App\Entity\Playlist();
        $this->assertInstanceOf(\App\Entity\Playlist::class, $playlist);
        $this->assertNull($playlist->getName());
        $this->assertInstanceOf(\DateTimeInterface::class, $playlist->getCreatedAt());
        
        $playlist->setName('My Playlist');
        $this->assertSame('My Playlist', $playlist->getName());
    }

    public function testUserBasicCreation(): void
    {
        $user = new \App\Entity\User();
        $this->assertInstanceOf(\App\Entity\User::class, $user);
        $this->assertNull($user->getUsername());
        $this->assertSame(['ROLE_USER'], $user->getRoles());
        
        $user->setUsername('testuser');
        $this->assertSame('testuser', $user->getUsername());
        $this->assertSame('testuser', $user->getUserIdentifier());
        
        $user->setFullName('Test User');
        $this->assertSame('Test User', $user->getFullName());
        
        $user->setPassword('hashedpass');
        $this->assertSame('hashedpass', $user->getPassword());
    }

    public function testMediaBasicCreation(): void
    {
        $media = new \App\Entity\Media();
        $this->assertInstanceOf(\App\Entity\Media::class, $media);
        $this->assertInstanceOf(\DateTimeInterface::class, $media->getUploadedAt());
        
        $media->setFileName('song.mp3');
        $this->assertSame('song.mp3', $media->getFileName());
        
        $media->setMimeType('audio/mpeg');
        $this->assertSame('audio/mpeg', $media->getMimeType());
        
        $media->setFileSize(1024);
        $this->assertSame(1024, $media->getFileSize());
    }

    public function testTagBasicCreation(): void
    {
        $tag = new \App\Entity\Tag();
        $this->assertInstanceOf(\App\Entity\Tag::class, $tag);
        $this->assertNull($tag->getName());
        
        $tag->setName('uplifting');
        $this->assertSame('uplifting', $tag->getName());
    }

    public function testAlbumRatingBasicCreation(): void
    {
        $rating = new \App\Entity\AlbumRating();
        $this->assertInstanceOf(\App\Entity\AlbumRating::class, $rating);
        $this->assertInstanceOf(\DateTimeInterface::class, $rating->getRatedAt());
        
        $rating->setRateType('like');
        $this->assertSame('like', $rating->getRateType());
    }

    public function testTrackRatingBasicCreation(): void
    {
        $rating = new \App\Entity\TrackRating();
        $this->assertInstanceOf(\App\Entity\TrackRating::class, $rating);
        $this->assertInstanceOf(\DateTimeInterface::class, $rating->getRatedAt());
        
        $rating->setRateType('love');
        $this->assertSame('love', $rating->getRateType());
    }

    public function testPlaylistRatingBasicCreation(): void
    {
        $rating = new \App\Entity\PlaylistRating();
        $this->assertInstanceOf(\App\Entity\PlaylistRating::class, $rating);
        $this->assertInstanceOf(\DateTimeInterface::class, $rating->getRatedAt());
        
        $rating->setRateType('favorite');
        $this->assertSame('favorite', $rating->getRateType());
    }
}