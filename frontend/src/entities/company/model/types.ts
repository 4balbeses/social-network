export interface Company {
  id: number;
  name: string;
  description: string | null;
  industry: string | null;
  stage: string | null;
  website: string | null;
  location: string | null;
  foundedAt: string;
  valuation: string | null;
  founder: {
    id: number;
    username: string;
    fullName: string;
  };
  pitches: Pitch[];
  followers: User[];
  investments: Investment[];
  createdAt: string;
  updatedAt: string;
}

export interface Pitch {
  id: number;
  title: string;
  description: string;
  fundingGoal: string | null;
  currentFunding: string | null;
  deckUrl: string | null;
  videoUrl: string | null;
  isActive: boolean;
  deadline: string | null;
  company: Company;
  investments: Investment[];
  comments: PitchComment[];
  likedBy: User[];
  createdAt: string;
  updatedAt: string;
}

export interface Investment {
  id: number;
  amount: string;
  investmentType: string | null;
  equityPercentage: string | null;
  investor: User;
  company: Company;
  pitch: Pitch | null;
  status: 'pending' | 'accepted' | 'declined' | 'completed';
  terms: string | null;
  createdAt: string;
  updatedAt: string;
}

export interface PitchComment {
  id: number;
  content: string;
  author: User;
  pitch: Pitch;
  createdAt: string;
  updatedAt: string;
}

export interface User {
  id: number;
  username: string;
  fullName: string;
  userType: 'entrepreneur' | 'investor' | 'mentor' | 'advisor';
  bio: string | null;
  location: string | null;
  linkedin: string | null;
  twitter: string | null;
  industry: string | null;
  expertise: string | null;
  registeredAt: string;
}