import { useUnit } from 'effector-react'
import { Link } from 'react-router-dom'
import { $user, $isAuthenticated } from '../../entities/user'
import { Button } from '../../shared/ui/Button'

export function Header() {
  const [user, isAuthenticated] = useUnit([$user, $isAuthenticated])

  return (
    <header className="bg-card border-b border-border nav-gradient">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          <div className="flex items-center">
            <Link to="/" className="text-xl font-bold text-card-foreground">
              🎵 Social Network
            </Link>
          </div>
          
          <nav className="flex space-x-6">
            <Link to="/" className="text-muted-foreground hover:text-card-foreground transition-colors">
              Home
            </Link>
            <Link to="/feed" className="text-muted-foreground hover:text-card-foreground transition-colors">
              Feed
            </Link>
            <Link to="/artists" className="text-muted-foreground hover:text-card-foreground transition-colors">
              Artists
            </Link>
            <Link to="/albums" className="text-muted-foreground hover:text-card-foreground transition-colors">
              Albums
            </Link>
            <Link to="/tracks" className="text-muted-foreground hover:text-card-foreground transition-colors">
              Tracks
            </Link>
            <Link to="/playlists" className="text-muted-foreground hover:text-card-foreground transition-colors">
              Playlists
            </Link>
            <Link to="/users" className="text-muted-foreground hover:text-card-foreground transition-colors">
              Users
            </Link>
          </nav>
          
          <div className="flex items-center space-x-4">
            {isAuthenticated ? (
              <div className="flex items-center space-x-4">
                <span className="text-sm text-muted-foreground">
                  Welcome, {user?.username}
                </span>
                <Link to="/profile">
                  <Button variant="outline" size="sm">
                    Profile
                  </Button>
                </Link>
              </div>
            ) : (
              <Link to="/login">
                <Button size="sm">Login</Button>
              </Link>
            )}
          </div>
        </div>
      </div>
    </header>
  )
}