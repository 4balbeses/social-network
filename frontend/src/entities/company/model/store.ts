import { createEvent, createStore, createEffect } from 'effector';
import type { Company } from './types';
import { companyApi } from '../api';

export const fetchCompanies = createEvent();
export const fetchCompanyById = createEvent<number>();
export const followCompany = createEvent<number>();
export const unfollowCompany = createEvent<number>();

export const fetchCompaniesFx = createEffect(async () => {
  return companyApi.getAll();
});

export const fetchCompanyByIdFx = createEffect(async (id: number) => {
  return companyApi.getById(id);
});

export const followCompanyFx = createEffect(async (id: number) => {
  return companyApi.follow(id);
});

export const unfollowCompanyFx = createEffect(async (id: number) => {
  return companyApi.unfollow(id);
});

export const $companies = createStore<Company[]>([])
  .on(fetchCompaniesFx.doneData, (_, companies) => companies);

export const $selectedCompany = createStore<Company | null>(null)
  .on(fetchCompanyByIdFx.doneData, (_, company) => company);

export const $companiesLoading = createStore(false)
  .on(fetchCompaniesFx.pending, (_, loading) => loading)
  .on(fetchCompanyByIdFx.pending, (_, loading) => loading);

// Connect events to effects
fetchCompanies.watch(() => fetchCompaniesFx());
fetchCompanyById.watch((id) => fetchCompanyByIdFx(id));
followCompany.watch((id) => followCompanyFx(id));
unfollowCompany.watch((id) => unfollowCompanyFx(id));