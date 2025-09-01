import { createStore, createEvent, createEffect, sample } from 'effector'

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

export const fetchAlbums = createEffect<void, Album[]>()
export const fetchAlbum = createEffect<number, Album>()
export const createAlbum = createEffect<CreateAlbumDto, Album>()
export const updateAlbum = createEffect<{ id: number; data: UpdateAlbumDto }, Album>()
export const deleteAlbum = createEffect<number, void>()

export const setAlbums = createEvent<Album[]>()
export const setCurrentAlbum = createEvent<Album | null>()
export const addAlbum = createEvent<Album>()
export const updateAlbumInStore = createEvent<Album>()
export const removeAlbum = createEvent<number>()
export const clearCurrentAlbum = createEvent()

export const $albums = createStore<Album[]>([])
  .on(setAlbums, (_, albums) => albums)
  .on(addAlbum, (albums, album) => [...albums, album])
  .on(updateAlbumInStore, (albums, updatedAlbum) => 
    albums.map(album => album.id === updatedAlbum.id ? updatedAlbum : album)
  )
  .on(removeAlbum, (albums, albumId) => 
    albums.filter(album => album.id !== albumId)
  )

export const $currentAlbum = createStore<Album | null>(null)
  .on(setCurrentAlbum, (_, album) => album)
  .on(clearCurrentAlbum, () => null)

export const $albumsLoading = createStore(false)
  .on([fetchAlbums, fetchAlbum, createAlbum, updateAlbum, deleteAlbum], () => true)
  .on([fetchAlbums.done, fetchAlbum.done, createAlbum.done, updateAlbum.done, deleteAlbum.done], () => false)
  .on([fetchAlbums.fail, fetchAlbum.fail, createAlbum.fail, updateAlbum.fail, deleteAlbum.fail], () => false)

sample({
  clock: fetchAlbums.doneData,
  target: setAlbums,
})

sample({
  clock: fetchAlbum.doneData,
  target: setCurrentAlbum,
})

sample({
  clock: createAlbum.doneData,
  target: addAlbum,
})

sample({
  clock: updateAlbum.doneData,
  target: updateAlbumInStore,
})

sample({
  clock: deleteAlbum.done,
  fn: ({ params }) => params,
  target: removeAlbum,
})