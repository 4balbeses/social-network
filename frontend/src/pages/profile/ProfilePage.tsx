import { useUnit } from 'effector-react'
import { $user, $isAuthenticated, clearUser } from '../../entities/user'
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
      <main className="max-w-4xl mx-auto px-4 py-8">
        <div className="bg-card p-8 rounded-lg shadow-md">
          <div className="flex items-center justify-between mb-6">
            <h1 className="text-2xl font-bold text-card-foreground">Profile</h1>
            <Button variant="outline" onClick={handleLogout}>
              Logout
            </Button>
          </div>
          
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-muted-foreground">Username</label>
              <p className="mt-1 text-lg text-card-foreground">{user?.username}</p>
            </div>
            <div>
              <label className="block text-sm font-medium text-muted-foreground">Full Name</label>
              <p className="mt-1 text-lg text-card-foreground">{user?.fullName}</p>
            </div>
          </div>
          
          <div className="mt-8">
            <h2 className="text-lg font-semibold mb-4">Music Statistics</h2>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="bg-muted p-4 rounded-lg">
                <p className="text-2xl font-bold text-blue-600">0</p>
                <p className="text-sm text-muted-foreground">Playlists Created</p>
              </div>
              <div className="bg-muted p-4 rounded-lg">
                <p className="text-2xl font-bold text-green-600">0</p>
                <p className="text-sm text-muted-foreground">Tracks Rated</p>
              </div>
              <div className="bg-muted p-4 rounded-lg">
                <p className="text-2xl font-bold text-purple-600">0</p>
                <p className="text-sm text-muted-foreground">Favorite Artists</p>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  )
}