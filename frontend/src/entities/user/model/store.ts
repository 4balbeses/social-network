import { createCrudStore } from '@/shared/lib/create-crud-store';
import { type User } from '@/shared/types';

export const userStore = createCrudStore<User>('/users');