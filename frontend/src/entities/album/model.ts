import { albumStore } from './model/store'

export interface Album {
  id: number
  name: string
  description?: string
  createdAt: string
  artist: {
    id: number
    fullName: string
  }
}

export interface CreateAlbumDto {
  name: string
  description?: string
  artistId: number
}

export type UpdateAlbumDto = Partial<CreateAlbumDto>

// Export the working CRUD store
export const fetchAlbums = albumStore.fetchItems
export const fetchAlbum = albumStore.fetchItems // For single album, we'll use fetchItems for now
export const createAlbum = albumStore.createItem
export const updateAlbum = albumStore.updateItem
export const deleteAlbum = albumStore.deleteItem

export const $albums = albumStore.$items
export const $currentAlbum = albumStore.$items // Simplified for now
export const $albumsLoading = albumStore.$loading
export const $albumsError = albumStore.$error

// Export the store for other uses
export { albumStore }