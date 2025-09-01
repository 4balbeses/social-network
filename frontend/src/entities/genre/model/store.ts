import { createCrudStore } from '@/shared/lib/create-crud-store';
import { type Genre } from '@/shared/types';

export const genreStore = createCrudStore<Genre>('/genres');