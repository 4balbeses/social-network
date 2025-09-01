import { createStore, createEvent, createEffect, sample } from 'effector'

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

export const fetchTracks = createEffect<void, Track[]>()
export const fetchTrack = createEffect<number, Track>()
export const createTrack = createEffect<CreateTrackDto, Track>()
export const updateTrack = createEffect<{ id: number; data: UpdateTrackDto }, Track>()
export const deleteTrack = createEffect<number, void>()

export const setTracks = createEvent<Track[]>()
export const setCurrentTrack = createEvent<Track | null>()
export const addTrack = createEvent<Track>()
export const updateTrackInStore = createEvent<Track>()
export const removeTrack = createEvent<number>()
export const clearCurrentTrack = createEvent()

export const $tracks = createStore<Track[]>([])
  .on(setTracks, (_, tracks) => tracks)
  .on(addTrack, (tracks, track) => [...tracks, track])
  .on(updateTrackInStore, (tracks, updatedTrack) => 
    tracks.map(track => track.id === updatedTrack.id ? updatedTrack : track)
  )
  .on(removeTrack, (tracks, trackId) => 
    tracks.filter(track => track.id !== trackId)
  )

export const $currentTrack = createStore<Track | null>(null)
  .on(setCurrentTrack, (_, track) => track)
  .on(clearCurrentTrack, () => null)

export const $tracksLoading = createStore(false)
  .on([fetchTracks, fetchTrack, createTrack, updateTrack, deleteTrack], () => true)
  .on([fetchTracks.done, fetchTrack.done, createTrack.done, updateTrack.done, deleteTrack.done], () => false)
  .on([fetchTracks.fail, fetchTrack.fail, createTrack.fail, updateTrack.fail, deleteTrack.fail], () => false)

sample({
  clock: fetchTracks.doneData,
  target: setTracks,
})

sample({
  clock: fetchTrack.doneData,
  target: setCurrentTrack,
})

sample({
  clock: createTrack.doneData,
  target: addTrack,
})

sample({
  clock: updateTrack.doneData,
  target: updateTrackInStore,
})

sample({
  clock: deleteTrack.done,
  fn: ({ params }) => params,
  target: removeTrack,
})