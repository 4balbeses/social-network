import { createStore, createEvent, createEffect, sample } from 'effector'

export interface Artist {
  id: number
  fullName: string
  description?: string
}

export interface CreateArtistDto {
  fullName: string
  description?: string
}

export type UpdateArtistDto = Partial<CreateArtistDto>

export const fetchArtists = createEffect<void, Artist[]>()
export const fetchArtist = createEffect<number, Artist>()
export const createArtist = createEffect<CreateArtistDto, Artist>()
export const updateArtist = createEffect<{ id: number; data: UpdateArtistDto }, Artist>()
export const deleteArtist = createEffect<number, void>()

export { artistStore } from './model/store'

export const setArtists = createEvent<Artist[]>()
export const setCurrentArtist = createEvent<Artist | null>()
export const addArtist = createEvent<Artist>()
export const updateArtistInStore = createEvent<Artist>()
export const removeArtist = createEvent<number>()
export const clearCurrentArtist = createEvent()

export const $artists = createStore<Artist[]>([])
  .on(setArtists, (_, artists) => artists)
  .on(addArtist, (artists, artist) => [...artists, artist])
  .on(updateArtistInStore, (artists, updatedArtist) => 
    artists.map(artist => artist.id === updatedArtist.id ? updatedArtist : artist)
  )
  .on(removeArtist, (artists, artistId) => 
    artists.filter(artist => artist.id !== artistId)
  )

export const $currentArtist = createStore<Artist | null>(null)
  .on(setCurrentArtist, (_, artist) => artist)
  .on(clearCurrentArtist, () => null)

export const $artistsLoading = createStore(false)
  .on([fetchArtists, fetchArtist, createArtist, updateArtist, deleteArtist], () => true)
  .on([fetchArtists.done, fetchArtist.done, createArtist.done, updateArtist.done, deleteArtist.done], () => false)
  .on([fetchArtists.fail, fetchArtist.fail, createArtist.fail, updateArtist.fail, deleteArtist.fail], () => false)

sample({
  clock: fetchArtists.doneData,
  target: setArtists,
})

sample({
  clock: fetchArtist.doneData,
  target: setCurrentArtist,
})

sample({
  clock: createArtist.doneData,
  target: addArtist,
})

sample({
  clock: updateArtist.doneData,
  target: updateArtistInStore,
})

sample({
  clock: deleteArtist.done,
  fn: ({ params }) => params,
  target: removeArtist,
})