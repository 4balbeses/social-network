import React from 'react';
import { Company } from '../../entities/company/model/types';
import { followCompany, unfollowCompany } from '../../entities/company';

interface CompanyCardProps {
  company: Company;
  compact?: boolean;
}

export const CompanyCard: React.FC<CompanyCardProps> = ({ company, compact = false }) => {
  const handleFollow = () => {
    followCompany(company.id);
  };

  const handleUnfollow = () => {
    unfollowCompany(company.id);
  };

  const formatValuation = (valuation: string | null) => {
    if (!valuation) return 'Not disclosed';
    const val = parseInt(valuation);
    if (val >= 1000000000) return `$${(val / 1000000000).toFixed(1)}B`;
    if (val >= 1000000) return `$${(val / 1000000).toFixed(1)}M`;
    if (val >= 1000) return `$${(val / 1000).toFixed(1)}K`;
    return `$${val}`;
  };

  const getStageColor = (stage: string | null) => {
    switch (stage) {
      case 'seed': return 'bg-green-100 text-green-800';
      case 'series-a': return 'bg-blue-100 text-blue-800';
      case 'series-b': return 'bg-purple-100 text-purple-800';
      case 'series-c': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  if (compact) {
    return (
      <div className="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:border-gray-300 transition-colors">
        <div className="flex-1 min-w-0">
          <h4 className="font-medium text-gray-900 truncate">{company.name}</h4>
          <p className="text-sm text-gray-500 truncate">{company.industry}</p>
          {company.stage && (
            <span className={`inline-block px-2 py-1 text-xs rounded-full ${getStageColor(company.stage)} mt-1`}>
              {company.stage.replace('-', ' ').toUpperCase()}
            </span>
          )}
        </div>
        <button
          onClick={handleFollow}
          className="ml-3 px-3 py-1 text-xs bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
        >
          Follow
        </button>
      </div>
    );
  }

  return (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
      <div className="flex items-start justify-between mb-4">
        <div className="flex-1">
          <div className="flex items-center gap-3 mb-2">
            <h3 className="text-xl font-semibold text-gray-900">{company.name}</h3>
            {company.stage && (
              <span className={`px-2 py-1 text-xs rounded-full ${getStageColor(company.stage)}`}>
                {company.stage.replace('-', ' ').toUpperCase()}
              </span>
            )}
          </div>
          <p className="text-gray-600 mb-2">{company.description}</p>
          <div className="flex items-center gap-4 text-sm text-gray-500">
            {company.industry && (
              <span className="flex items-center">
                🏢 {company.industry}
              </span>
            )}
            {company.location && (
              <span className="flex items-center">
                📍 {company.location}
              </span>
            )}
            <span className="flex items-center">
              💰 {formatValuation(company.valuation)}
            </span>
          </div>
        </div>
        <div className="flex gap-2">
          <button
            onClick={handleFollow}
            className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
          >
            Follow
          </button>
          {company.website && (
            <a
              href={company.website}
              target="_blank"
              rel="noopener noreferrer"
              className="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
            >
              Visit
            </a>
          )}
        </div>
      </div>

      <div className="flex items-center justify-between text-sm text-gray-500">
        <div className="flex items-center gap-4">
          <span>👥 {company.followers?.length || 0} followers</span>
          <span>💼 {company.pitches?.length || 0} pitches</span>
          <span>💵 {company.investments?.length || 0} investments</span>
        </div>
        <span>Founded {new Date(company.foundedAt).getFullYear()}</span>
      </div>
    </div>
  );
};