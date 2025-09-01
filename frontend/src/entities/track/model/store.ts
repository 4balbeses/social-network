import { createCrudStore } from '@/shared/lib/create-crud-store';
import { type Track } from '@/shared/types';

export const trackStore = createCrudStore<Track>('/tracks');