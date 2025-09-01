import { apiClient } from './base'
import type {
  User, CreateUserDto, UpdateUserDto, LoginDto,
  Track, CreateTrackDto, UpdateTrackDto,
  Album, CreateAlbumDto, UpdateAlbumDto,
  Artist, CreateArtistDto, UpdateArtistDto,
  Playlist, CreatePlaylistDto, UpdatePlaylistDto,
  Genre, CreateGenreDto, UpdateGenreDto,
  Media, UploadMediaDto,
  Tag, CreateTagDto, UpdateTagDto,
  TrackRating, AlbumRating, PlaylistRating,
  CreateTrackRatingDto, CreateAlbumRatingDto, CreatePlaylistRatingDto
} from '../../entities'

// User API
export const userApi = {
  fetchAll: () => apiClient.get<User[]>('/users'),
  fetchById: (id: number) => apiClient.get<User>(`/users/${id}`),
  login: (data: LoginDto) => apiClient.post<{ user: User; token: string }>('/auth/login', data),
  register: (data: CreateUserDto) => apiClient.post<User>('/users', data),
  update: (id: number, data: UpdateUserDto) => apiClient.patch<User>(`/users/${id}`, data),
  delete: (id: number) => apiClient.delete<void>(`/users/${id}`),
}

// Track API
export const trackApi = {
  fetchAll: () => apiClient.get<Track[]>('/tracks'),
  fetchById: (id: number) => apiClient.get<Track>(`/tracks/${id}`),
  create: (data: CreateTrackDto) => apiClient.post<Track>('/tracks', data),
  update: (id: number, data: UpdateTrackDto) => apiClient.patch<Track>(`/tracks/${id}`, data),
  delete: (id: number) => apiClient.delete<void>(`/tracks/${id}`),
}

// Album API
export const albumApi = {
  fetchAll: () => apiClient.get<Album[]>('/albums'),
  fetchById: (id: number) => apiClient.get<Album>(`/albums/${id}`),
  create: (data: CreateAlbumDto) => apiClient.post<Album>('/albums', data),
  update: (id: number, data: UpdateAlbumDto) => apiClient.patch<Album>(`/albums/${id}`, data),
  delete: (id: number) => apiClient.delete<void>(`/albums/${id}`),
}

// Artist API
export const artistApi = {
  fetchAll: () => apiClient.get<Artist[]>('/artists'),
  fetchById: (id: number) => apiClient.get<Artist>(`/artists/${id}`),
  create: (data: CreateArtistDto) => apiClient.post<Artist>('/artists', data),
  update: (id: number, data: UpdateArtistDto) => apiClient.patch<Artist>(`/artists/${id}`, data),
  delete: (id: number) => apiClient.delete<void>(`/artists/${id}`),
}

// Playlist API
export const playlistApi = {
  fetchAll: () => apiClient.get<Playlist[]>('/playlists'),
  fetchById: (id: number) => apiClient.get<Playlist>(`/playlists/${id}`),
  fetchByUser: (userId: number) => apiClient.get<Playlist[]>(`/users/${userId}/playlists`),
  create: (data: CreatePlaylistDto) => apiClient.post<Playlist>('/playlists', data),
  update: (id: number, data: UpdatePlaylistDto) => apiClient.patch<Playlist>(`/playlists/${id}`, data),
  delete: (id: number) => apiClient.delete<void>(`/playlists/${id}`),
}

// Genre API
export const genreApi = {
  fetchAll: () => apiClient.get<Genre[]>('/genres'),
  fetchById: (id: number) => apiClient.get<Genre>(`/genres/${id}`),
  create: (data: CreateGenreDto) => apiClient.post<Genre>('/genres', data),
  update: (id: number, data: UpdateGenreDto) => apiClient.patch<Genre>(`/genres/${id}`, data),
  delete: (id: number) => apiClient.delete<void>(`/genres/${id}`),
}

// Media API
export const mediaApi = {
  fetchAll: () => apiClient.get<Media[]>('/media'),
  fetchById: (id: number) => apiClient.get<Media>(`/media/${id}`),
  upload: ({ file }: UploadMediaDto) => apiClient.uploadFile<Media>('/media/upload', file),
  delete: (id: number) => apiClient.delete<void>(`/media/${id}`),
}

// Tag API
export const tagApi = {
  fetchAll: () => apiClient.get<Tag[]>('/tags'),
  fetchById: (id: number) => apiClient.get<Tag>(`/tags/${id}`),
  fetchByUser: (userId: number) => apiClient.get<Tag[]>(`/users/${userId}/tags`),
  create: (data: CreateTagDto) => apiClient.post<Tag>('/tags', data),
  update: (id: number, data: UpdateTagDto) => apiClient.patch<Tag>(`/tags/${id}`, data),
  delete: (id: number) => apiClient.delete<void>(`/tags/${id}`),
}

// Rating API
export const ratingApi = {
  track: {
    fetchByTrack: (trackId: number) => apiClient.get<TrackRating[]>(`/tracks/${trackId}/ratings`),
    create: (data: CreateTrackRatingDto) => apiClient.post<TrackRating>('/track-ratings', data),
    delete: (userId: number, trackId: number) => apiClient.delete<void>(`/track-ratings/${userId}/${trackId}`),
  },
  album: {
    fetchByAlbum: (albumId: number) => apiClient.get<AlbumRating[]>(`/albums/${albumId}/ratings`),
    create: (data: CreateAlbumRatingDto) => apiClient.post<AlbumRating>('/album-ratings', data),
    delete: (userId: number, albumId: number) => apiClient.delete<void>(`/album-ratings/${userId}/${albumId}`),
  },
  playlist: {
    fetchByPlaylist: (playlistId: number) => apiClient.get<PlaylistRating[]>(`/playlists/${playlistId}/ratings`),
    create: (data: CreatePlaylistRatingDto) => apiClient.post<PlaylistRating>('/playlist-ratings', data),
    delete: (userId: number, playlistId: number) => apiClient.delete<void>(`/playlist-ratings/${userId}/${playlistId}`),
  },
}