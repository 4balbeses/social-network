import { createCrudStore } from '@/shared/lib/create-crud-store';
import { type Artist } from '@/shared/types';

export const artistStore = createCrudStore<Artist>('/artists');