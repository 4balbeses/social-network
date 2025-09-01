import { createStore, createEvent, createEffect, sample } from 'effector'
import { userApi } from '../../shared/api/entities'

export interface User {
  id: number
  username: string
  fullName: string
  registeredAt: string
  roles: string[]
}

export interface CreateUserDto {
  username: string
  fullName: string
  password: string
}

export type UpdateUserDto = Partial<Omit<CreateUserDto, 'password'>>

export interface LoginDto {
  username: string
  password: string
}

export const fetchUsers = createEffect<void, User[]>(async () => {
  return await userApi.fetchAll()
})

export const fetchUser = createEffect<number, User>(async (id) => {
  return await userApi.fetchById(id)
})

export const loginUser = createEffect<LoginDto, { user: User; token: string }>(async (data) => {
  return await userApi.login(data)
})

export const registerUser = createEffect<CreateUserDto, User>(async (data) => {
  return await userApi.register(data)
})

export const updateUser = createEffect<{ id: number; data: UpdateUserDto }, User>(async ({ id, data }) => {
  return await userApi.update(id, data)
})

export const deleteUser = createEffect<number, void>(async (id) => {
  return await userApi.delete(id)
})

export { userStore } from './model/store'

export const setUser = createEvent<User>()
export const setUsers = createEvent<User[]>()
export const setCurrentUser = createEvent<User | null>()
export const clearUser = createEvent()
export const addUser = createEvent<User>()
export const updateUserInStore = createEvent<User>()
export const removeUser = createEvent<number>()

export const $user = createStore<User | null>(null)
  .on(setUser, (_, user) => user)
  .on(clearUser, () => null)

export const $users = createStore<User[]>([])
  .on(setUsers, (_, users) => users)
  .on(addUser, (users, user) => [...users, user])
  .on(updateUserInStore, (users, updatedUser) => 
    users.map(user => user.id === updatedUser.id ? updatedUser : user)
  )
  .on(removeUser, (users, userId) => 
    users.filter(user => user.id !== userId)
  )

export const $currentUser = createStore<User | null>(null)
  .on(setCurrentUser, (_, user) => user)

export const $isAuthenticated = $user.map(user => !!user)

export const $usersLoading = createStore(false)
  .on([fetchUsers, fetchUser, loginUser, registerUser, updateUser, deleteUser], () => true)
  .on([fetchUsers.done, fetchUser.done, loginUser.done, registerUser.done, updateUser.done, deleteUser.done], () => false)
  .on([fetchUsers.fail, fetchUser.fail, loginUser.fail, registerUser.fail, updateUser.fail, deleteUser.fail], () => false)

sample({
  clock: fetchUsers.doneData,
  target: setUsers,
})

sample({
  clock: fetchUser.doneData,
  target: setCurrentUser,
})

sample({
  clock: loginUser.doneData,
  fn: ({ user }) => user,
  target: setUser,
})

sample({
  clock: registerUser.doneData,
  target: addUser,
})

sample({
  clock: updateUser.doneData,
  target: updateUserInStore,
})

sample({
  clock: deleteUser.done,
  fn: ({ params }) => params,
  target: removeUser,
})