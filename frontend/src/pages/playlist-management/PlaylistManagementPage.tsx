import { Header } from '../../widgets/header'
import { PlaylistManagement } from '../../features/playlist-management'

export function PlaylistManagementPage() {
  return (
    <div>
      <Header />
      <main className="max-w-6xl mx-auto px-4 py-8">
        <div className="mb-6">
          <h1 className="text-2xl font-bold text-gray-900">Playlist Management</h1>
          <p className="text-gray-600 mt-2">Manage music playlists</p>
        </div>
        <PlaylistManagement />
      </main>
    </div>
  )
}