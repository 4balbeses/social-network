import type { Company } from '../model/types';

const API_BASE = 'http://localhost:18888/api';

export const companyApi = {
  async getAll(): Promise<Company[]> {
    const response = await fetch(`${API_BASE}/companies`);
    if (!response.ok) throw new Error('Failed to fetch companies');
    const data = await response.json();
    return Array.isArray(data.member) ? data.member : [];
  },

  async getById(id: number): Promise<Company> {
    const response = await fetch(`${API_BASE}/companies/${id}`);
    if (!response.ok) throw new Error('Failed to fetch company');
    return response.json();
  },

  async getByIndustry(industry: string): Promise<Company[]> {
    const response = await fetch(`${API_BASE}/companies/industry/${industry}`);
    if (!response.ok) throw new Error('Failed to fetch companies by industry');
    return response.json();
  },

  async getByStage(stage: string): Promise<Company[]> {
    const response = await fetch(`${API_BASE}/companies/stage/${stage}`);
    if (!response.ok) throw new Error('Failed to fetch companies by stage');
    return response.json();
  },

  async getPopular(): Promise<Company[]> {
    const response = await fetch(`${API_BASE}/companies/popular`);
    if (!response.ok) throw new Error('Failed to fetch popular companies');
    return response.json();
  },

  async follow(id: number): Promise<{ status: string }> {
    const response = await fetch(`${API_BASE}/companies/${id}/follow`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
    });
    if (!response.ok) throw new Error('Failed to follow company');
    return response.json();
  },

  async unfollow(id: number): Promise<{ status: string }> {
    const response = await fetch(`${API_BASE}/companies/${id}/unfollow`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
    });
    if (!response.ok) throw new Error('Failed to unfollow company');
    return response.json();
  },
};