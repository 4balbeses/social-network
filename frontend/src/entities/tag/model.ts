import { createStore, createEvent, createEffect, sample } from 'effector'

export interface Tag {
  id: number
  name: string
  author: {
    id: number
    username: string
  }
}

export interface CreateTagDto {
  name: string
}

export type UpdateTagDto = Partial<CreateTagDto>

export const fetchTags = createEffect<void, Tag[]>()
export const fetchTag = createEffect<number, Tag>()
export const fetchUserTags = createEffect<number, Tag[]>()
export const createTag = createEffect<CreateTagDto, Tag>()
export const updateTag = createEffect<{ id: number; data: UpdateTagDto }, Tag>()
export const deleteTag = createEffect<number, void>()

export const setTags = createEvent<Tag[]>()
export const setCurrentTag = createEvent<Tag | null>()
export const setUserTags = createEvent<Tag[]>()
export const addTag = createEvent<Tag>()
export const updateTagInStore = createEvent<Tag>()
export const removeTag = createEvent<number>()
export const clearCurrentTag = createEvent()

export const $tags = createStore<Tag[]>([])
  .on(setTags, (_, tags) => tags)
  .on(addTag, (tags, tag) => [...tags, tag])
  .on(updateTagInStore, (tags, updatedTag) => 
    tags.map(tag => tag.id === updatedTag.id ? updatedTag : tag)
  )
  .on(removeTag, (tags, tagId) => 
    tags.filter(tag => tag.id !== tagId)
  )

export const $userTags = createStore<Tag[]>([])
  .on(setUserTags, (_, tags) => tags)

export const $currentTag = createStore<Tag | null>(null)
  .on(setCurrentTag, (_, tag) => tag)
  .on(clearCurrentTag, () => null)

export const $tagsLoading = createStore(false)
  .on([fetchTags, fetchTag, fetchUserTags, createTag, updateTag, deleteTag], () => true)
  .on([fetchTags.done, fetchTag.done, fetchUserTags.done, createTag.done, updateTag.done, deleteTag.done], () => false)
  .on([fetchTags.fail, fetchTag.fail, fetchUserTags.fail, createTag.fail, updateTag.fail, deleteTag.fail], () => false)

sample({
  clock: fetchTags.doneData,
  target: setTags,
})

sample({
  clock: fetchUserTags.doneData,
  target: setUserTags,
})

sample({
  clock: fetchTag.doneData,
  target: setCurrentTag,
})

sample({
  clock: createTag.doneData,
  target: addTag,
})

sample({
  clock: updateTag.doneData,
  target: updateTagInStore,
})

sample({
  clock: deleteTag.done,
  fn: ({ params }) => params,
  target: removeTag,
})