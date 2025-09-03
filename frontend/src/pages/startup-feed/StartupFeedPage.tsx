import React, { useEffect } from 'react';
import { useUnit } from 'effector-react';
import { fetchCompanies, $companies, $companiesLoading } from '../../entities/company';
import { fetchTrendingPitches, $trendingPitches, $pitchesLoading } from '../../entities/pitch';
import { CompanyCard } from '../../features/company-card';
import { PitchCard } from '../../features/pitch-card';

export const StartupFeedPage: React.FC = () => {
  const companies = useUnit($companies);
  const trendingPitches = useUnit($trendingPitches);
  const companiesLoading = useUnit($companiesLoading);
  const pitchesLoading = useUnit($pitchesLoading);

  useEffect(() => {
    fetchCompanies();
    fetchTrendingPitches();
  }, []);

  return (
    <div className="min-h-screen gradient-bg">
      <div className="container mx-auto px-4 py-8">
        <header className="mb-8">
          <h1 className="text-3xl font-bold text-foreground mb-2">
            Startup Network
          </h1>
          <p className="text-muted-foreground">
            Discover innovative startups, connect with founders, and explore investment opportunities
          </p>
        </header>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Main Feed */}
          <div className="lg:col-span-2">
            <div className="mb-8">
              <h2 className="text-xl font-semibold text-foreground mb-4">
                Trending Pitches
              </h2>
              {pitchesLoading ? (
                <div className="text-center py-8">
                  <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mx-auto"></div>
                  <p className="mt-2 text-muted-foreground">Loading trending pitches...</p>
                </div>
              ) : (
                <div className="space-y-6">
                  {Array.isArray(trendingPitches) ? trendingPitches.map((pitch) => (
                    <PitchCard key={pitch.id} pitch={pitch} />
                  )) : null}
                </div>
              )}
            </div>
          </div>

          {/* Sidebar */}
          <div className="lg:col-span-1">
            <div className="card p-6 mb-6">
              <h3 className="text-lg font-semibold text-card-foreground mb-4">
                Featured Companies
              </h3>
              {companiesLoading ? (
                <div className="text-center py-4">
                  <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600 mx-auto"></div>
                </div>
              ) : (
                <div className="space-y-4">
                  {Array.isArray(companies) ? companies.slice(0, 5).map((company) => (
                    <CompanyCard key={company.id} company={company} compact />
                  )) : null}
                </div>
              )}
            </div>

            <div className="card p-6">
              <h3 className="text-lg font-semibold text-card-foreground mb-4">
                Quick Actions
              </h3>
              <div className="space-y-2">
                <button className="w-full text-left px-3 py-2 rounded-md hover:bg-accent hover:text-accent-foreground text-muted-foreground transition-colors">
                  🚀 Browse All Startups
                </button>
                <button className="w-full text-left px-3 py-2 rounded-md hover:bg-accent hover:text-accent-foreground text-muted-foreground transition-colors">
                  💰 Investment Opportunities
                </button>
                <button className="w-full text-left px-3 py-2 rounded-md hover:bg-accent hover:text-accent-foreground text-muted-foreground transition-colors">
                  🌟 Top Pitches This Week
                </button>
                <button className="w-full text-left px-3 py-2 rounded-md hover:bg-accent hover:text-accent-foreground text-muted-foreground transition-colors">
                  👥 Connect with Founders
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};