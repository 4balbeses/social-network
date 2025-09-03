import React, { useState } from 'react';
import { Pitch } from '../../entities/company/model/types';
import { likePitch, unlikePitch } from '../../entities/pitch';
import { pitchApi } from '../../entities/pitch/api';

interface PitchCardProps {
  pitch: Pitch;
}

export const PitchCard: React.FC<PitchCardProps> = ({ pitch }) => {
  const [showInvestmentModal, setShowInvestmentModal] = useState(false);
  const [investmentAmount, setInvestmentAmount] = useState('');

  const handleLike = () => {
    likePitch(pitch.id);
  };

  const handleUnlike = () => {
    unlikePitch(pitch.id);
  };

  const handleInvest = async () => {
    if (!investmentAmount || isNaN(Number(investmentAmount))) {
      alert('Please enter a valid investment amount');
      return;
    }

    try {
      await pitchApi.invest(pitch.id, investmentAmount);
      alert('Investment proposal submitted successfully!');
      setShowInvestmentModal(false);
      setInvestmentAmount('');
    } catch (error) {
      alert('Failed to submit investment proposal');
    }
  };

  const formatAmount = (amount: string | null) => {
    if (!amount) return 'Not disclosed';
    const val = parseInt(amount);
    if (val >= 1000000) return `$${(val / 1000000).toFixed(1)}M`;
    if (val >= 1000) return `$${(val / 1000).toFixed(0)}K`;
    return `$${val}`;
  };

  const getFundingProgress = () => {
    if (!pitch.fundingGoal || !pitch.currentFunding) return 0;
    return (parseInt(pitch.currentFunding) / parseInt(pitch.fundingGoal)) * 100;
  };

  const timeLeft = pitch.deadline ? Math.ceil((new Date(pitch.deadline).getTime() - Date.now()) / (1000 * 60 * 60 * 24)) : null;

  return (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
      <div className="flex items-start justify-between mb-4">
        <div className="flex-1">
          <div className="flex items-center gap-3 mb-2">
            <h3 className="text-xl font-semibold text-gray-900">{pitch.title}</h3>
            {pitch.isActive && (
              <span className="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                ACTIVE
              </span>
            )}
          </div>
          <p className="text-gray-600 mb-3">{pitch.description}</p>
          <div className="flex items-center gap-2 text-sm text-gray-500 mb-3">
            <span className="font-medium">By {pitch.company.name}</span>
            <span>•</span>
            <span>{pitch.company.industry}</span>
            {timeLeft && timeLeft > 0 && (
              <>
                <span>•</span>
                <span className="text-orange-600 font-medium">{timeLeft} days left</span>
              </>
            )}
          </div>
        </div>
      </div>

      {/* Funding Progress */}
      {pitch.fundingGoal && (
        <div className="mb-4">
          <div className="flex justify-between text-sm text-gray-600 mb-2">
            <span>Funding Progress</span>
            <span>{formatAmount(pitch.currentFunding)} / {formatAmount(pitch.fundingGoal)}</span>
          </div>
          <div className="w-full bg-gray-200 rounded-full h-2">
            <div 
              className="bg-blue-600 h-2 rounded-full transition-all duration-300" 
              style={{ width: `${Math.min(getFundingProgress(), 100)}%` }}
            />
          </div>
          <p className="text-xs text-gray-500 mt-1">{getFundingProgress().toFixed(1)}% funded</p>
        </div>
      )}

      {/* Actions */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-4">
          <button
            onClick={handleLike}
            className="flex items-center gap-2 text-gray-600 hover:text-red-600 transition-colors"
          >
            <span className="text-lg">❤️</span>
            <span className="text-sm">{pitch.likedBy?.length || 0}</span>
          </button>
          <button className="flex items-center gap-2 text-gray-600 hover:text-blue-600 transition-colors">
            <span className="text-lg">💬</span>
            <span className="text-sm">{pitch.comments?.length || 0}</span>
          </button>
          <button className="flex items-center gap-2 text-gray-600 hover:text-green-600 transition-colors">
            <span className="text-lg">📈</span>
            <span className="text-sm">{pitch.investments?.length || 0} investors</span>
          </button>
        </div>

        <div className="flex gap-2">
          {pitch.deckUrl && (
            <a
              href={pitch.deckUrl}
              target="_blank"
              rel="noopener noreferrer"
              className="px-3 py-1 text-sm border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
            >
              View Deck
            </a>
          )}
          {pitch.videoUrl && (
            <a
              href={pitch.videoUrl}
              target="_blank"
              rel="noopener noreferrer"
              className="px-3 py-1 text-sm border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
            >
              Watch Pitch
            </a>
          )}
          <button
            onClick={() => setShowInvestmentModal(true)}
            className="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
          >
            Invest
          </button>
        </div>
      </div>

      {/* Investment Modal */}
      {showInvestmentModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <h3 className="text-lg font-semibold mb-4">Invest in {pitch.title}</h3>
            <div className="mb-4">
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Investment Amount ($)
              </label>
              <input
                type="number"
                value={investmentAmount}
                onChange={(e) => setInvestmentAmount(e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Enter amount..."
              />
            </div>
            <div className="flex gap-3">
              <button
                onClick={handleInvest}
                className="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
              >
                Submit Proposal
              </button>
              <button
                onClick={() => setShowInvestmentModal(false)}
                className="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};