import { createStore, createEvent, createEffect, sample } from 'effector'

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

export const fetchPlaylists = createEffect<void, Playlist[]>()
export const fetchPlaylist = createEffect<number, Playlist>()
export const fetchUserPlaylists = createEffect<number, Playlist[]>()
export const createPlaylist = createEffect<CreatePlaylistDto, Playlist>()
export const updatePlaylist = createEffect<{ id: number; data: UpdatePlaylistDto }, Playlist>()
export const deletePlaylist = createEffect<number, void>()

export const setPlaylists = createEvent<Playlist[]>()
export const setCurrentPlaylist = createEvent<Playlist | null>()
export const setUserPlaylists = createEvent<Playlist[]>()
export const addPlaylist = createEvent<Playlist>()
export const updatePlaylistInStore = createEvent<Playlist>()
export const removePlaylist = createEvent<number>()
export const clearCurrentPlaylist = createEvent()

export const $playlists = createStore<Playlist[]>([])
  .on(setPlaylists, (_, playlists) => playlists)
  .on(addPlaylist, (playlists, playlist) => [...playlists, playlist])
  .on(updatePlaylistInStore, (playlists, updatedPlaylist) => 
    playlists.map(playlist => playlist.id === updatedPlaylist.id ? updatedPlaylist : playlist)
  )
  .on(removePlaylist, (playlists, playlistId) => 
    playlists.filter(playlist => playlist.id !== playlistId)
  )

export const $userPlaylists = createStore<Playlist[]>([])
  .on(setUserPlaylists, (_, playlists) => playlists)

export const $currentPlaylist = createStore<Playlist | null>(null)
  .on(setCurrentPlaylist, (_, playlist) => playlist)
  .on(clearCurrentPlaylist, () => null)

export const $playlistsLoading = createStore(false)
  .on([fetchPlaylists, fetchPlaylist, fetchUserPlaylists, createPlaylist, updatePlaylist, deletePlaylist], () => true)
  .on([fetchPlaylists.done, fetchPlaylist.done, fetchUserPlaylists.done, createPlaylist.done, updatePlaylist.done, deletePlaylist.done], () => false)
  .on([fetchPlaylists.fail, fetchPlaylist.fail, fetchUserPlaylists.fail, createPlaylist.fail, updatePlaylist.fail, deletePlaylist.fail], () => false)

sample({
  clock: fetchPlaylists.doneData,
  target: setPlaylists,
})

sample({
  clock: fetchUserPlaylists.doneData,
  target: setUserPlaylists,
})

sample({
  clock: fetchPlaylist.doneData,
  target: setCurrentPlaylist,
})

sample({
  clock: createPlaylist.doneData,
  target: addPlaylist,
})

sample({
  clock: updatePlaylist.doneData,
  target: updatePlaylistInStore,
})

sample({
  clock: deletePlaylist.done,
  fn: ({ params }) => params,
  target: removePlaylist,
})