<?php

namespace App\Tests\Entity;

use App\Entity\Track;
use App\Entity\Media;
use App\Entity\Genre;
use App\Entity\TrackPlaylist;
use App\Entity\TrackAlbum;
use App\Entity\TrackTag;
use App\Entity\TrackRating;
use PHPUnit\Framework\TestCase;

class TrackTest extends TestCase
{
    public function testCreateTrack(): void
    {
        $track = new Track();
        
        $this->assertNull($track->getId());
        $this->assertNull($track->getName());
        $this->assertNull($track->getDescription());
        $this->assertNull($track->getTrackFile());
        $this->assertNull($track->getGenre());
        $this->assertCount(0, $track->getTrackPlaylists());
        $this->assertCount(0, $track->getTrackAlbums());
        $this->assertCount(0, $track->getTrackTags());
        $this->assertCount(0, $track->getTrackRatings());
    }

    public function testSetName(): void
    {
        $track = new Track();
        $name = 'My Song';
        
        $result = $track->setName($name);
        
        $this->assertSame($track, $result);
        $this->assertSame($name, $track->getName());
    }

    public function testSetDescription(): void
    {
        $track = new Track();
        $description = 'A great song about life';
        
        $result = $track->setDescription($description);
        
        $this->assertSame($track, $result);
        $this->assertSame($description, $track->getDescription());
    }

    public function testSetNullDescription(): void
    {
        $track = new Track();
        
        $result = $track->setDescription(null);
        
        $this->assertSame($track, $result);
        $this->assertNull($track->getDescription());
    }

    public function testSetTrackFile(): void
    {
        $track = new Track();
        $media = new Media();
        
        $result = $track->setTrackFile($media);
        
        $this->assertSame($track, $result);
        $this->assertSame($media, $track->getTrackFile());
    }

    public function testSetNullTrackFile(): void
    {
        $track = new Track();
        
        $result = $track->setTrackFile(null);
        
        $this->assertSame($track, $result);
        $this->assertNull($track->getTrackFile());
    }

    public function testSetGenre(): void
    {
        $track = new Track();
        $genre = new Genre();
        
        $result = $track->setGenre($genre);
        
        $this->assertSame($track, $result);
        $this->assertSame($genre, $track->getGenre());
    }

    public function testSetNullGenre(): void
    {
        $track = new Track();
        
        $result = $track->setGenre(null);
        
        $this->assertSame($track, $result);
        $this->assertNull($track->getGenre());
    }

    public function testAddTrackPlaylist(): void
    {
        $track = new Track();
        $trackPlaylist = new TrackPlaylist();
        
        $result = $track->addTrackPlaylist($trackPlaylist);
        
        $this->assertSame($track, $result);
        $this->assertTrue($track->getTrackPlaylists()->contains($trackPlaylist));
        $this->assertSame($track, $trackPlaylist->getTrack());
    }

    public function testAddSameTrackPlaylistTwice(): void
    {
        $track = new Track();
        $trackPlaylist = new TrackPlaylist();
        
        $track->addTrackPlaylist($trackPlaylist);
        $track->addTrackPlaylist($trackPlaylist);
        
        $this->assertCount(1, $track->getTrackPlaylists());
    }

    public function testRemoveTrackPlaylist(): void
    {
        $track = new Track();
        $trackPlaylist = new TrackPlaylist();
        
        $track->addTrackPlaylist($trackPlaylist);
        $result = $track->removeTrackPlaylist($trackPlaylist);
        
        $this->assertSame($track, $result);
        $this->assertFalse($track->getTrackPlaylists()->contains($trackPlaylist));
    }

    public function testAddTrackAlbum(): void
    {
        $track = new Track();
        $trackAlbum = new TrackAlbum();
        
        $result = $track->addTrackAlbum($trackAlbum);
        
        $this->assertSame($track, $result);
        $this->assertTrue($track->getTrackAlbums()->contains($trackAlbum));
        $this->assertSame($track, $trackAlbum->getTrack());
    }

    public function testRemoveTrackAlbum(): void
    {
        $track = new Track();
        $trackAlbum = new TrackAlbum();
        
        $track->addTrackAlbum($trackAlbum);
        $result = $track->removeTrackAlbum($trackAlbum);
        
        $this->assertSame($track, $result);
        $this->assertFalse($track->getTrackAlbums()->contains($trackAlbum));
    }

    public function testAddTrackTag(): void
    {
        $track = new Track();
        $trackTag = new TrackTag();
        
        $result = $track->addTrackTag($trackTag);
        
        $this->assertSame($track, $result);
        $this->assertTrue($track->getTrackTags()->contains($trackTag));
        $this->assertSame($track, $trackTag->getTrack());
    }

    public function testRemoveTrackTag(): void
    {
        $track = new Track();
        $trackTag = new TrackTag();
        
        $track->addTrackTag($trackTag);
        $result = $track->removeTrackTag($trackTag);
        
        $this->assertSame($track, $result);
        $this->assertFalse($track->getTrackTags()->contains($trackTag));
    }

    public function testAddTrackRating(): void
    {
        $track = new Track();
        $trackRating = new TrackRating();
        
        $result = $track->addTrackRating($trackRating);
        
        $this->assertSame($track, $result);
        $this->assertTrue($track->getTrackRatings()->contains($trackRating));
        $this->assertSame($track, $trackRating->getRatedTrack());
    }

    public function testRemoveTrackRating(): void
    {
        $track = new Track();
        $trackRating = new TrackRating();
        
        $track->addTrackRating($trackRating);
        $result = $track->removeTrackRating($trackRating);
        
        $this->assertSame($track, $result);
        $this->assertFalse($track->getTrackRatings()->contains($trackRating));
    }
}