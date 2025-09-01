export interface User {
  id: number;
  username: string;
  fullName: string;
  registeredAt: string;
  roles: string[];
}

export interface Artist {
  id: number;
  fullName: string;
  description?: string;
}

export interface Album {
  id: number;
  name: string;
  description?: string;
  createdAt: string;
  artist: Artist;
}

export interface Genre {
  id: number;
  name: string;
  description?: string;
}

export interface Media {
  id: number;
  originalName: string;
  fileName: string;
  filePath: string;
  mimeType: string;
  fileSize: number;
  uploadedAt: string;
}

export interface Track {
  id: number;
  name: string;
  description?: string;
  trackFile: Media;
  genre: Genre;
}

export interface Tag {
  id: number;
  name: string;
  author: User;
}

export interface Playlist {
  id: number;
  name: string;
  description?: string;
  createdAt: string;
  isPublic: boolean;
  owner: User;
}

export interface Rating {
  id: number;
  rating: number;
  comment?: string;
  createdAt: string;
  ratingUser: User;
}

export interface AlbumRating extends Rating {
  ratedAlbum: Album;
}

export interface TrackRating extends Rating {
  ratedTrack: Track;
}

export interface PlaylistRating extends Rating {
  ratedPlaylist: Playlist;
}

export interface CreateUserDto {
  username: string;
  password: string;
  fullName: string;
}

export interface CreateArtistDto {
  fullName: string;
  description?: string;
}

export interface CreateAlbumDto {
  name: string;
  description?: string;
  artistId: number;
}

export interface CreateTrackDto {
  name: string;
  description?: string;
  genreId: number;
  trackFileId: number;
}

export interface CreatePlaylistDto {
  name: string;
  description?: string;
  isPublic: boolean;
}

export interface CreateGenreDto {
  name: string;
  description?: string;
}

export interface CreateTagDto {
  name: string;
}

export interface CreateRatingDto {
  rating: number;
  comment?: string;
}

export type EntityType = 'users' | 'artists' | 'albums' | 'tracks' | 'playlists' | 'genres' | 'tags' | 'media';