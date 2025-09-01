import { createCrudStore } from '@/shared/lib/create-crud-store';
import { type Playlist } from '@/shared/types';

export const playlistStore = createCrudStore<Playlist>('/playlists');