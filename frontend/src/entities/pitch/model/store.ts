import { createEvent, createStore, createEffect } from 'effector';
import type { Pitch } from '../../company/model/types';
import { pitchApi } from '../api';

export const fetchPitches = createEvent();
export const fetchPitchById = createEvent<number>();
export const fetchTrendingPitches = createEvent();
export const likePitch = createEvent<number>();
export const unlikePitch = createEvent<number>();

export const fetchPitchesFx = createEffect(async () => {
  return pitchApi.getAll();
});

export const fetchPitchByIdFx = createEffect(async (id: number) => {
  return pitchApi.getById(id);
});

export const fetchTrendingPitchesFx = createEffect(async () => {
  return pitchApi.getTrending();
});

export const likePitchFx = createEffect(async (id: number) => {
  return pitchApi.like(id);
});

export const unlikePitchFx = createEffect(async (id: number) => {
  return pitchApi.unlike(id);
});

export const $pitches = createStore<Pitch[]>([])
  .on(fetchPitchesFx.doneData, (_, pitches) => pitches);

export const $trendingPitches = createStore<Pitch[]>([])
  .on(fetchTrendingPitchesFx.doneData, (_, pitches) => pitches);

export const $selectedPitch = createStore<Pitch | null>(null)
  .on(fetchPitchByIdFx.doneData, (_, pitch) => pitch);

export const $pitchesLoading = createStore(false)
  .on(fetchPitchesFx.pending, (_, loading) => loading)
  .on(fetchPitchByIdFx.pending, (_, loading) => loading)
  .on(fetchTrendingPitchesFx.pending, (_, loading) => loading);

// Connect events to effects
fetchPitches.watch(() => fetchPitchesFx());
fetchPitchById.watch((id) => fetchPitchByIdFx(id));
fetchTrendingPitches.watch(() => fetchTrendingPitchesFx());
likePitch.watch((id) => likePitchFx(id));
unlikePitch.watch((id) => unlikePitchFx(id));