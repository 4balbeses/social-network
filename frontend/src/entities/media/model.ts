import { createStore, createEvent, createEffect, sample } from 'effector'

export interface Media {
  id: number
  originalName: string
  fileName: string
  filePath: string
  mimeType: string
  fileSize: number
  uploadedAt: string
}

export interface UploadMediaDto {
  file: File
}

export const fetchMedia = createEffect<void, Media[]>()
export const fetchMediaById = createEffect<number, Media>()
export const uploadMedia = createEffect<UploadMediaDto, Media>()
export const deleteMedia = createEffect<number, void>()

export const setMediaFiles = createEvent<Media[]>()
export const setCurrentMedia = createEvent<Media | null>()
export const addMedia = createEvent<Media>()
export const removeMedia = createEvent<number>()
export const clearCurrentMedia = createEvent()

export const $mediaFiles = createStore<Media[]>([])
  .on(setMediaFiles, (_, media) => media)
  .on(addMedia, (media, newMedia) => [...media, newMedia])
  .on(removeMedia, (media, mediaId) => 
    media.filter(m => m.id !== mediaId)
  )

export const $currentMedia = createStore<Media | null>(null)
  .on(setCurrentMedia, (_, media) => media)
  .on(clearCurrentMedia, () => null)

export const $mediaLoading = createStore(false)
  .on([fetchMedia, fetchMediaById, uploadMedia, deleteMedia], () => true)
  .on([fetchMedia.done, fetchMediaById.done, uploadMedia.done, deleteMedia.done], () => false)
  .on([fetchMedia.fail, fetchMediaById.fail, uploadMedia.fail, deleteMedia.fail], () => false)

sample({
  clock: fetchMedia.doneData,
  target: setMediaFiles,
})

sample({
  clock: fetchMediaById.doneData,
  target: setCurrentMedia,
})

sample({
  clock: uploadMedia.doneData,
  target: addMedia,
})

sample({
  clock: deleteMedia.done,
  fn: ({ params }) => params,
  target: removeMedia,
})