import { useUnit } from 'effector-react'
import { Link } from 'react-router-dom'
import { $user, $isAuthenticated } from '../../entities/user'
import { Button } from '../../shared/ui/Button'

export function Header() {
  const [user, isAuthenticated] = useUnit([$user, $isAuthenticated])

  return (
    <header className="bg-white border-b border-gray-200">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          <div className="flex items-center">
            <Link to="/" className="text-xl font-bold text-gray-900">
              SocialMusic
            </Link>
          </div>
          
          <nav className="flex space-x-6">
            <Link to="/" className="text-gray-700 hover:text-gray-900">
              Home
            </Link>
            <Link to="/tracks" className="text-gray-700 hover:text-gray-900">
              Browse
            </Link>
            
            {/* Management Dropdown - simplified as individual links for now */}
            <div className="flex space-x-4">
              <span className="text-gray-500 font-medium">Manage:</span>
              <Link to="/manage/tracks" className="text-sm text-blue-600 hover:text-blue-800">
                Tracks
              </Link>
              <Link to="/manage/albums" className="text-sm text-blue-600 hover:text-blue-800">
                Albums
              </Link>
              <Link to="/manage/artists" className="text-sm text-blue-600 hover:text-blue-800">
                Artists
              </Link>
              <Link to="/manage/playlists" className="text-sm text-blue-600 hover:text-blue-800">
                Playlists
              </Link>
              <Link to="/manage/genres" className="text-sm text-blue-600 hover:text-blue-800">
                Genres
              </Link>
            </div>
          </nav>
          
          <div className="flex items-center space-x-4">
            {isAuthenticated ? (
              <div className="flex items-center space-x-4">
                <span className="text-sm text-gray-700">
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