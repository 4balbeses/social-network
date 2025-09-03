import { type ReactNode } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { useUnit } from 'effector-react';
import { cn } from '@/shared/lib/utils';
import { ThemeToggle } from '@/shared/ui/theme-toggle';
import { $user, $isAuthenticated } from '@/entities/user';
import { Button } from '@/shared/ui/Button';

interface LayoutProps {
  children: ReactNode;
}

const navigation = [
  { name: 'Home', href: '/' },
  { name: 'Feed', href: '/feed' },
  { name: 'Users', href: '/users' },
  { name: 'Artists', href: '/artists' },
  { name: 'Albums', href: '/albums' },
  { name: 'Tracks', href: '/tracks' },
  { name: 'Playlists', href: '/playlists' },
];

export const Layout = ({ children }: LayoutProps) => {
  const location = useLocation();
  const [user, isAuthenticated] = useUnit([$user, $isAuthenticated]);

  return (
    <div className="min-h-screen gradient-bg">
      <nav className="nav-gradient shadow-2xl border-b border-border/50 sticky top-0 z-50 backdrop-blur-xl">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between h-20">
            <div className="flex">
              <div className="flex-shrink-0 flex items-center">
                <Link to="/" className="text-2xl font-bold text-card-foreground hover:scale-105 transition-transform duration-300 flex items-center gap-2">
                  <span className="animate-float">🎵</span> 
                  <span className="bg-gradient-to-r from-primary-600 to-primary-400 bg-clip-text text-transparent">
                    SocialMusic
                  </span>
                </Link>
              </div>
              <div className="ml-8 flex space-x-1">
                {navigation.map((item) => (
                  <Link
                    key={item.name}
                    to={item.href}
                    className={cn(
                      'inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 relative overflow-hidden',
                      location.pathname === item.href
                        ? 'bg-primary-500/10 text-primary-600 shadow-md border border-primary-500/20 before:absolute before:inset-0 before:bg-gradient-to-r before:from-primary-500/20 before:to-transparent'
                        : 'text-muted-foreground hover:bg-accent/50 hover:text-card-foreground hover:shadow-sm transform hover:-translate-y-0.5'
                    )}
                  >
                    <span className="relative z-10">{item.name}</span>
                  </Link>
                ))}
              </div>
            </div>
            <div className="flex items-center space-x-4">
              <div className="smooth-bounce">
                <ThemeToggle />
              </div>
              {isAuthenticated ? (
                <div className="flex items-center space-x-4">
                  <div className="text-sm text-muted-foreground bg-accent/30 px-3 py-1 rounded-full backdrop-blur-sm">
                    Welcome, {user?.username}
                  </div>
                  <Link to="/profile">
                    <Button variant="outline" size="sm" className="smooth-bounce">
                      Profile
                    </Button>
                  </Link>
                </div>
              ) : (
                <Link to="/login">
                  <Button size="sm" className="smooth-bounce">Login</Button>
                </Link>
              )}
            </div>
          </div>
        </div>
      </nav>

      <main className="max-w-7xl mx-auto relative animate-fade-in-up">
        {children}
      </main>
      
      {/* Floating decoration */}
      <div className="fixed bottom-8 right-8 z-40 pointer-events-none">
        <div className="w-16 h-16 rounded-full bg-gradient-to-br from-primary-500/20 to-accent/20 animate-float blur-xl"></div>
      </div>
    </div>
  );
};