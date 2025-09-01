import { useUnit } from 'effector-react'
import { $user, $isAuthenticated, clearUser } from '../../entities/user'
import { Header } from '../../widgets/header'
import { Button } from '../../shared/ui/Button'
import { useNavigate } from 'react-router-dom'

export function ProfilePage() {
  const [user, isAuthenticated] = useUnit([$user, $isAuthenticated])
  const handleClearUser = useUnit(clearUser)
  const navigate = useNavigate()

  if (!isAuthenticated) {
    navigate('/login')
    return null
  }

  const handleLogout = () => {
    handleClearUser()
    navigate('/')
  }

  return (
    <div>
      <Header />
      <main className="max-w-4xl mx-auto px-4 py-8">
        <div className="bg-white p-8 rounded-lg shadow-md">
          <div className="flex items-center justify-between mb-6">
            <h1 className="text-2xl font-bold text-gray-900">Profile</h1>
            <Button variant="outline" onClick={handleLogout}>
              Logout
            </Button>
          </div>
          
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700">Username</label>
              <p className="mt-1 text-lg text-gray-900">{user?.username}</p>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700">Full Name</label>
              <p className="mt-1 text-lg text-gray-900">{user?.fullName}</p>
            </div>
          </div>
          
          <div className="mt-8">
            <h2 className="text-lg font-semibold mb-4">Music Statistics</h2>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="bg-gray-50 p-4 rounded-lg">
                <p className="text-2xl font-bold text-blue-600">0</p>
                <p className="text-sm text-gray-600">Playlists Created</p>
              </div>
              <div className="bg-gray-50 p-4 rounded-lg">
                <p className="text-2xl font-bold text-green-600">0</p>
                <p className="text-sm text-gray-600">Tracks Rated</p>
              </div>
              <div className="bg-gray-50 p-4 rounded-lg">
                <p className="text-2xl font-bold text-purple-600">0</p>
                <p className="text-sm text-gray-600">Favorite Artists</p>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  )
}