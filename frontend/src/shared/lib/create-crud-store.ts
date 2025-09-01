import { createStore, createEvent, createEffect, sample } from 'effector';
import { apiClient } from '@/shared/api/base';

export interface CrudState<T> {
  items: T[];
  loading: boolean;
  error: string | null;
}

export interface CrudStore<T> {
  $items: ReturnType<typeof createStore<T[]>>;
  $loading: ReturnType<typeof createStore<boolean>>;
  $error: ReturnType<typeof createStore<string | null>>;
  fetchItems: ReturnType<typeof createEvent<void>>;
  createItem: ReturnType<typeof createEvent<Omit<T, 'id'>>>;
  updateItem: ReturnType<typeof createEvent<T>>;
  deleteItem: ReturnType<typeof createEvent<number>>;
  clearError: ReturnType<typeof createEvent<void>>;
}

export function createCrudStore<T extends { id: number }>(
  endpoint: string
): CrudStore<T> {
  const fetchItems = createEvent<void>();
  const createItem = createEvent<Omit<T, 'id'>>();
  const updateItem = createEvent<T>();
  const deleteItem = createEvent<number>();
  const clearError = createEvent<void>();

  const fetchItemsFx = createEffect<void, T[]>(async () => {
    return await apiClient.get<T[]>(endpoint);
  });

  const createItemFx = createEffect<Omit<T, 'id'>, T>(async (data) => {
    return await apiClient.post<T>(endpoint, data);
  });

  const updateItemFx = createEffect<T, T>(async (item) => {
    return await apiClient.put<T>(`${endpoint}/${item.id}`, item);
  });

  const deleteItemFx = createEffect<number, void>(async (id) => {
    await apiClient.delete(`${endpoint}/${id}`);
  });

  const $items = createStore<T[]>([])
    .on(fetchItemsFx.doneData, (_, items) => items)
    .on(createItemFx.doneData, (items, newItem) => [...items, newItem])
    .on(updateItemFx.doneData, (items, updatedItem) =>
      items.map((item) => (item.id === updatedItem.id ? updatedItem : item))
    )
    .on(deleteItemFx.done, (items, { params: id }) =>
      items.filter((item) => item.id !== id)
    );

  const $loading = createStore(false)
    .on([fetchItemsFx, createItemFx, updateItemFx, deleteItemFx], () => true)
    .on(
      [
        fetchItemsFx.finally,
        createItemFx.finally,
        updateItemFx.finally,
        deleteItemFx.finally,
      ],
      () => false
    );

  const $error = createStore<string | null>(null)
    .on(
      [
        fetchItemsFx.failData,
        createItemFx.failData,
        updateItemFx.failData,
        deleteItemFx.failData,
      ],
      (_, error) => typeof error === 'string' ? error : (error?.message || 'An error occurred')
    )
    .reset(clearError)
    .reset([fetchItemsFx, createItemFx, updateItemFx, deleteItemFx]);

  sample({
    clock: fetchItems,
    target: fetchItemsFx,
  });

  sample({
    clock: createItem,
    target: createItemFx,
  });

  sample({
    clock: updateItem,
    target: updateItemFx,
  });

  sample({
    clock: deleteItem,
    target: deleteItemFx,
  });

  return {
    $items,
    $loading,
    $error,
    fetchItems,
    createItem,
    updateItem,
    deleteItem,
    clearError,
  };
}