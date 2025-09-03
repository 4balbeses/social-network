import type { Pitch } from '../../company/model/types';

const API_BASE = 'http://localhost:18888/api';

export const pitchApi = {
  async getAll(): Promise<Pitch[]> {
    const response = await fetch(`${API_BASE}/pitches`);
    if (!response.ok) throw new Error('Failed to fetch pitches');
    return response.json();
  },

  async getById(id: number): Promise<Pitch> {
    const response = await fetch(`${API_BASE}/pitches/${id}`);
    if (!response.ok) throw new Error('Failed to fetch pitch');
    return response.json();
  },

  async getTrending(): Promise<Pitch[]> {
    const response = await fetch(`${API_BASE}/pitches`);
    if (!response.ok) throw new Error('Failed to fetch trending pitches');
    const data = await response.json();
    return Array.isArray(data.member) ? data.member : [];
  },

  async getByCompany(companyId: number): Promise<Pitch[]> {
    const response = await fetch(`${API_BASE}/pitches/company/${companyId}`);
    if (!response.ok) throw new Error('Failed to fetch company pitches');
    return response.json();
  },

  async like(id: number): Promise<{ status: string }> {
    const response = await fetch(`${API_BASE}/pitches/${id}/like`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
    });
    if (!response.ok) throw new Error('Failed to like pitch');
    return response.json();
  },

  async unlike(id: number): Promise<{ status: string }> {
    const response = await fetch(`${API_BASE}/pitches/${id}/unlike`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
    });
    if (!response.ok) throw new Error('Failed to unlike pitch');
    return response.json();
  },

  async invest(id: number, amount: string): Promise<{ status: string; amount: string; pitch_id: number }> {
    const response = await fetch(`${API_BASE}/pitches/${id}/invest`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ amount }),
    });
    if (!response.ok) throw new Error('Failed to create investment proposal');
    return response.json();
  },
};