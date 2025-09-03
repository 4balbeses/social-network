<?php

namespace App\Tests\Entity;

use App\Entity\Media;
use App\Entity\Track;
use PHPUnit\Framework\TestCase;

class MediaTest extends TestCase
{
    public function testCreateMedia(): void
    {
        $media = new Media();
        
        $this->assertNull($media->getId());
        $this->assertNull($media->getFileName());
        $this->assertNull($media->getFilePath());
        $this->assertNull($media->getMimeType());
        $this->assertNull($media->getFileSize());
        $this->assertInstanceOf(\DateTimeInterface::class, $media->getUploadedAt());
        $this->assertCount(0, $media->getTracks());
    }

    public function testSetFileName(): void
    {
        $media = new Media();
        $fileName = 'song.mp3';
        
        $result = $media->setFileName($fileName);
        
        $this->assertSame($media, $result);
        $this->assertSame($fileName, $media->getFileName());
    }

    public function testSetFilePath(): void
    {
        $media = new Media();
        $filePath = '/uploads/audio/song.mp3';
        
        $result = $media->setFilePath($filePath);
        
        $this->assertSame($media, $result);
        $this->assertSame($filePath, $media->getFilePath());
    }

    public function testSetMimeType(): void
    {
        $media = new Media();
        $mimeType = 'audio/mpeg';
        
        $result = $media->setMimeType($mimeType);
        
        $this->assertSame($media, $result);
        $this->assertSame($mimeType, $media->getMimeType());
    }

    public function testSetFileSize(): void
    {
        $media = new Media();
        $fileSize = 1024;
        
        $result = $media->setFileSize($fileSize);
        
        $this->assertSame($media, $result);
        $this->assertSame($fileSize, $media->getFileSize());
    }

    public function testSetUploadedAt(): void
    {
        $media = new Media();
        $date = new \DateTime('2024-01-01 12:00:00');
        
        $result = $media->setUploadedAt($date);
        
        $this->assertSame($media, $result);
        $this->assertSame($date, $media->getUploadedAt());
    }

    public function testAddTrack(): void
    {
        $media = new Media();
        $track = new Track();
        
        $result = $media->addTrack($track);
        
        $this->assertSame($media, $result);
        $this->assertTrue($media->getTracks()->contains($track));
        $this->assertSame($media, $track->getTrackFile());
    }

    public function testRemoveTrack(): void
    {
        $media = new Media();
        $track = new Track();
        
        $media->addTrack($track);
        $result = $media->removeTrack($track);
        
        $this->assertSame($media, $result);
        $this->assertFalse($media->getTracks()->contains($track));
        $this->assertNull($track->getTrackFile());
    }
}