import { createStore, createEvent, createEffect, sample } from 'effector'

export interface Genre {
  id: number
  name: string
  description?: string
}

export interface CreateGenreDto {
  name: string
  description?: string
}

export type UpdateGenreDto = Partial<CreateGenreDto>

export const fetchGenres = createEffect<void, Genre[]>()
export const fetchGenre = createEffect<number, Genre>()
export const createGenre = createEffect<CreateGenreDto, Genre>()
export const updateGenre = createEffect<{ id: number; data: UpdateGenreDto }, Genre>()
export const deleteGenre = createEffect<number, void>()

export const setGenres = createEvent<Genre[]>()
export const setCurrentGenre = createEvent<Genre | null>()
export const addGenre = createEvent<Genre>()
export const updateGenreInStore = createEvent<Genre>()
export const removeGenre = createEvent<number>()
export const clearCurrentGenre = createEvent()

export const $genres = createStore<Genre[]>([])
  .on(setGenres, (_, genres) => genres)
  .on(addGenre, (genres, genre) => [...genres, genre])
  .on(updateGenreInStore, (genres, updatedGenre) => 
    genres.map(genre => genre.id === updatedGenre.id ? updatedGenre : genre)
  )
  .on(removeGenre, (genres, genreId) => 
    genres.filter(genre => genre.id !== genreId)
  )

export const $currentGenre = createStore<Genre | null>(null)
  .on(setCurrentGenre, (_, genre) => genre)
  .on(clearCurrentGenre, () => null)

export const $genresLoading = createStore(false)
  .on([fetchGenres, fetchGenre, createGenre, updateGenre, deleteGenre], () => true)
  .on([fetchGenres.done, fetchGenre.done, createGenre.done, updateGenre.done, deleteGenre.done], () => false)
  .on([fetchGenres.fail, fetchGenre.fail, createGenre.fail, updateGenre.fail, deleteGenre.fail], () => false)

sample({
  clock: fetchGenres.doneData,
  target: setGenres,
})

sample({
  clock: fetchGenre.doneData,
  target: setCurrentGenre,
})

sample({
  clock: createGenre.doneData,
  target: addGenre,
})

sample({
  clock: updateGenre.doneData,
  target: updateGenreInStore,
})

sample({
  clock: deleteGenre.done,
  fn: ({ params }) => params,
  target: removeGenre,
})