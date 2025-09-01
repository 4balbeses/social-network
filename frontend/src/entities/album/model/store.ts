import { createCrudStore } from '@/shared/lib/create-crud-store';
import { type Album } from '@/shared/types';

export const albumStore = createCrudStore<Album>('/albums');