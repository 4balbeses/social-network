import { createStore, createEvent, createEffect, sample } from 'effector'

export interface TrackRating {
  ratingUser: {
    id: number
    username: string
  }
  ratedTrack: {
    id: number
    name: string
  }
  rateType: string
  ratedAt: string
}

export interface AlbumRating {
  ratingUser: {
    id: number
    username: string
  }
  ratedAlbum: {
    id: number
    name: string
  }
  rateType: string
  ratedAt: string
}

export interface PlaylistRating {
  ratingUser: {
    id: number
    username: string
  }
  ratedPlaylist: {
    id: number
    name: string
  }
  rateType: string
  ratedAt: string
}

export interface CreateTrackRatingDto {
  trackId: number
  rateType: string
}

export interface CreateAlbumRatingDto {
  albumId: number
  rateType: string
}

export interface CreatePlaylistRatingDto {
  playlistId: number
  rateType: string
}

export const fetchTrackRatings = createEffect<number, TrackRating[]>()
export const fetchAlbumRatings = createEffect<number, AlbumRating[]>()
export const fetchPlaylistRatings = createEffect<number, PlaylistRating[]>()
export const createTrackRating = createEffect<CreateTrackRatingDto, TrackRating>()
export const createAlbumRating = createEffect<CreateAlbumRatingDto, AlbumRating>()
export const createPlaylistRating = createEffect<CreatePlaylistRatingDto, PlaylistRating>()
export const deleteTrackRating = createEffect<{ userId: number; trackId: number }, void>()
export const deleteAlbumRating = createEffect<{ userId: number; albumId: number }, void>()
export const deletePlaylistRating = createEffect<{ userId: number; playlistId: number }, void>()

export const setTrackRatings = createEvent<TrackRating[]>()
export const setAlbumRatings = createEvent<AlbumRating[]>()
export const setPlaylistRatings = createEvent<PlaylistRating[]>()
export const addTrackRating = createEvent<TrackRating>()
export const addAlbumRating = createEvent<AlbumRating>()
export const addPlaylistRating = createEvent<PlaylistRating>()
export const removeTrackRating = createEvent<{ userId: number; trackId: number }>()
export const removeAlbumRating = createEvent<{ userId: number; albumId: number }>()
export const removePlaylistRating = createEvent<{ userId: number; playlistId: number }>()

export const $trackRatings = createStore<TrackRating[]>([])
  .on(setTrackRatings, (_, ratings) => ratings)
  .on(addTrackRating, (ratings, rating) => [...ratings, rating])
  .on(removeTrackRating, (ratings, { userId, trackId }) => 
    ratings.filter(r => !(r.ratingUser.id === userId && r.ratedTrack.id === trackId))
  )

export const $albumRatings = createStore<AlbumRating[]>([])
  .on(setAlbumRatings, (_, ratings) => ratings)
  .on(addAlbumRating, (ratings, rating) => [...ratings, rating])
  .on(removeAlbumRating, (ratings, { userId, albumId }) => 
    ratings.filter(r => !(r.ratingUser.id === userId && r.ratedAlbum.id === albumId))
  )

export const $playlistRatings = createStore<PlaylistRating[]>([])
  .on(setPlaylistRatings, (_, ratings) => ratings)
  .on(addPlaylistRating, (ratings, rating) => [...ratings, rating])
  .on(removePlaylistRating, (ratings, { userId, playlistId }) => 
    ratings.filter(r => !(r.ratingUser.id === userId && r.ratedPlaylist.id === playlistId))
  )

export const $ratingsLoading = createStore(false)
  .on([fetchTrackRatings, fetchAlbumRatings, fetchPlaylistRatings, createTrackRating, createAlbumRating, createPlaylistRating, deleteTrackRating, deleteAlbumRating, deletePlaylistRating], () => true)
  .on([fetchTrackRatings.done, fetchAlbumRatings.done, fetchPlaylistRatings.done, createTrackRating.done, createAlbumRating.done, createPlaylistRating.done, deleteTrackRating.done, deleteAlbumRating.done, deletePlaylistRating.done], () => false)
  .on([fetchTrackRatings.fail, fetchAlbumRatings.fail, fetchPlaylistRatings.fail, createTrackRating.fail, createAlbumRating.fail, createPlaylistRating.fail, deleteTrackRating.fail, deleteAlbumRating.fail, deletePlaylistRating.fail], () => false)

sample({
  clock: fetchTrackRatings.doneData,
  target: setTrackRatings,
})

sample({
  clock: fetchAlbumRatings.doneData,
  target: setAlbumRatings,
})

sample({
  clock: fetchPlaylistRatings.doneData,
  target: setPlaylistRatings,
})

sample({
  clock: createTrackRating.doneData,
  target: addTrackRating,
})

sample({
  clock: createAlbumRating.doneData,
  target: addAlbumRating,
})

sample({
  clock: createPlaylistRating.doneData,
  target: addPlaylistRating,
})

sample({
  clock: deleteTrackRating.done,
  fn: ({ params }) => params,
  target: removeTrackRating,
})

sample({
  clock: deleteAlbumRating.done,
  fn: ({ params }) => params,
  target: removeAlbumRating,
})

sample({
  clock: deletePlaylistRating.done,
  fn: ({ params }) => params,
  target: removePlaylistRating,
})