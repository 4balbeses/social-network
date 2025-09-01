export const API_BASE_URL = '/api';

export const API_ENDPOINTS = {
  users: `${API_BASE_URL}/users`,
  artists: `${API_BASE_URL}/artists`,
  albums: `${API_BASE_URL}/albums`,
  tracks: `${API_BASE_URL}/tracks`,
  playlists: `${API_BASE_URL}/playlists`,
  genres: `${API_BASE_URL}/genres`,
  tags: `${API_BASE_URL}/tags`,
  media: `${API_BASE_URL}/media`,
  albumRatings: `${API_BASE_URL}/album_ratings`,
  trackRatings: `${API_BASE_URL}/track_ratings`,
  playlistRatings: `${API_BASE_URL}/playlist_ratings`,
} as const;