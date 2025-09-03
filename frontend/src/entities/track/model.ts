import { trackStore } from './model/store'

export interface Track {
  id: number
  name: string
  description?: string
  trackFile: {
    id: number
    originalName: string
    filePath: string
  }
  genre: {
    id: number
    name: string
  }
}

export interface CreateTrackDto {
  name: string
  description?: string
  trackFileId: number
  genreId: number
}

export type UpdateTrackDto = Partial<CreateTrackDto>

// Export the working CRUD store
export const fetchTracks = trackStore.fetchItems
export const fetchTrack = trackStore.fetchItems // For single track, we'll use fetchItems for now
export const createTrack = trackStore.createItem
export const updateTrack = trackStore.updateItem
export const deleteTrack = trackStore.deleteItem

export const $tracks = trackStore.$items
export const $currentTrack = trackStore.$items // Simplified for now
export const $tracksLoading = trackStore.$loading
export const $tracksError = trackStore.$error

// Export the store for other uses
export { trackStore }