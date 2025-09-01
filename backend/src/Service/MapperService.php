<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Track;
use App\Entity\Album;
use App\Entity\Playlist;
use App\Entity\Artist;
use App\Entity\Genre;
use App\Entity\Tag;
use App\Entity\Media;
use App\DTO\Response\UserResponse;
use App\DTO\Response\TrackResponse;
use App\DTO\Response\AlbumResponse;
use App\DTO\Response\PlaylistResponse;
use App\DTO\Response\ArtistResponse;
use App\DTO\Response\GenreResponse;
use App\DTO\Response\TagResponse;
use App\DTO\Response\MediaResponse;

class MapperService
{
    public function userToResponse(User $user): UserResponse
    {
        $response = new UserResponse();
        $response->id = $user->getId();
        $response->username = $user->getUsername();
        $response->fullName = $user->getFullName();
        $response->roles = $user->getRoles();
        $response->registeredAt = $user->getRegisteredAt();
        
        foreach ($user->getPlaylists() as $playlist) {
            $response->playlists[] = [
                'id' => $playlist->getId(),
                'name' => $playlist->getName()
            ];
        }
        
        foreach ($user->getTags() as $tag) {
            $response->tags[] = [
                'id' => $tag->getId(),
                'name' => $tag->getName()
            ];
        }
        
        return $response;
    }

    public function trackToResponse(Track $track): TrackResponse
    {
        $response = new TrackResponse();
        $response->id = $track->getId();
        $response->name = $track->getName();
        $response->description = $track->getDescription();
        
        $response->trackFile = [
            'id' => $track->getTrackFile()->getId(),
            'filename' => $track->getTrackFile()->getFilename(),
            'filePath' => $track->getTrackFile()->getFilePath()
        ];
        
        $response->genre = [
            'id' => $track->getGenre()->getId(),
            'name' => $track->getGenre()->getName()
        ];
        
        foreach ($track->getTrackAlbums() as $trackAlbum) {
            $response->albums[] = [
                'id' => $trackAlbum->getAlbum()->getId(),
                'name' => $trackAlbum->getAlbum()->getName()
            ];
        }
        
        foreach ($track->getTrackPlaylists() as $trackPlaylist) {
            $response->playlists[] = [
                'id' => $trackPlaylist->getPlaylist()->getId(),
                'name' => $trackPlaylist->getPlaylist()->getName()
            ];
        }
        
        foreach ($track->getTrackTags() as $trackTag) {
            $response->tags[] = [
                'id' => $trackTag->getTag()->getId(),
                'name' => $trackTag->getTag()->getName()
            ];
        }
        
        $ratings = $track->getTrackRatings();
        if (count($ratings) > 0) {
            $total = 0;
            foreach ($ratings as $rating) {
                $total += $rating->getRating();
            }
            $response->averageRating = $total / count($ratings);
            $response->ratingsCount = count($ratings);
        }
        
        return $response;
    }

    public function albumToResponse(Album $album): AlbumResponse
    {
        $response = new AlbumResponse();
        $response->id = $album->getId();
        $response->name = $album->getName();
        $response->description = $album->getDescription();
        $response->createdAt = $album->getCreatedAt();
        
        $response->artist = [
            'id' => $album->getArtist()->getId(),
            'name' => $album->getArtist()->getName()
        ];
        
        foreach ($album->getTrackAlbums() as $trackAlbum) {
            $response->tracks[] = [
                'id' => $trackAlbum->getTrack()->getId(),
                'name' => $trackAlbum->getTrack()->getName()
            ];
        }
        
        $ratings = $album->getAlbumRatings();
        if (count($ratings) > 0) {
            $total = 0;
            foreach ($ratings as $rating) {
                $total += $rating->getRating();
            }
            $response->averageRating = $total / count($ratings);
            $response->ratingsCount = count($ratings);
        }
        
        return $response;
    }

    public function playlistToResponse(Playlist $playlist): PlaylistResponse
    {
        $response = new PlaylistResponse();
        $response->id = $playlist->getId();
        $response->name = $playlist->getName();
        $response->description = $playlist->getDescription();
        $response->isPublic = $playlist->isIsPublic();
        $response->createdAt = $playlist->getCreatedAt();
        
        $response->owner = [
            'id' => $playlist->getOwner()->getId(),
            'username' => $playlist->getOwner()->getUsername(),
            'fullName' => $playlist->getOwner()->getFullName()
        ];
        
        foreach ($playlist->getTrackPlaylists() as $trackPlaylist) {
            $response->tracks[] = [
                'id' => $trackPlaylist->getTrack()->getId(),
                'name' => $trackPlaylist->getTrack()->getName()
            ];
        }
        
        $ratings = $playlist->getPlaylistRatings();
        if (count($ratings) > 0) {
            $total = 0;
            foreach ($ratings as $rating) {
                $total += $rating->getRating();
            }
            $response->averageRating = $total / count($ratings);
            $response->ratingsCount = count($ratings);
        }
        
        return $response;
    }

    public function artistToResponse(Artist $artist): ArtistResponse
    {
        $response = new ArtistResponse();
        $response->id = $artist->getId();
        $response->fullName = $artist->getFullName();
        $response->description = $artist->getDescription();
        
        foreach ($artist->getAlbums() as $album) {
            $response->albums[] = [
                'id' => $album->getId(),
                'name' => $album->getName()
            ];
        }
        
        return $response;
    }

    public function genreToResponse(Genre $genre): GenreResponse
    {
        $response = new GenreResponse();
        $response->id = $genre->getId();
        $response->name = $genre->getName();
        $response->description = $genre->getDescription();
        
        foreach ($genre->getTracks() as $track) {
            $response->tracks[] = [
                'id' => $track->getId(),
                'name' => $track->getName()
            ];
        }
        
        return $response;
    }

    public function tagToResponse(Tag $tag): TagResponse
    {
        $response = new TagResponse();
        $response->id = $tag->getId();
        $response->name = $tag->getName();
        $response->authorId = $tag->getAuthor()->getId();
        $response->authorName = $tag->getAuthor()->getFullName();
        
        return $response;
    }

    public function mediaToResponse(Media $media): MediaResponse
    {
        $response = new MediaResponse();
        $response->id = $media->getId();
        $response->originalName = $media->getOriginalName();
        $response->fileName = $media->getFileName();
        $response->filePath = $media->getFilePath();
        $response->mimeType = $media->getMimeType();
        $response->fileSize = $media->getFileSize();
        $response->uploadedAt = $media->getUploadedAt();
        
        return $response;
    }
}