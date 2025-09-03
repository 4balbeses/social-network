import { playlistStore } from './model/store'

export interface Playlist {
  id: number
  name: string
  description?: string
  createdAt: string
  isPublic: boolean
  owner: {
    id: number
    username: string
  }
}

export interface CreatePlaylistDto {
  name: string
  description?: string
  isPublic?: boolean
}

export type UpdatePlaylistDto = Partial<CreatePlaylistDto>

// Export the working CRUD store
export const fetchPlaylists = playlistStore.fetchItems
export const fetchPlaylist = playlistStore.fetchItems // For single playlist, we'll use fetchItems for now
export const fetchUserPlaylists = playlistStore.fetchItems // Simplified for now
export const createPlaylist = playlistStore.createItem
export const updatePlaylist = playlistStore.updateItem
export const deletePlaylist = playlistStore.deleteItem

export const $playlists = playlistStore.$items
export const $userPlaylists = playlistStore.$items // Simplified for now
export const $currentPlaylist = playlistStore.$items // Simplified for now
export const $playlistsLoading = playlistStore.$loading
export const $playlistsError = playlistStore.$error

// Export the store for other uses
export { playlistStore }