import { useState, useEffect } from 'react'
import { useUnit } from 'effector-react'
import { $playlists, $playlistsLoading, fetchPlaylists, createPlaylist, updatePlaylist, deletePlaylist, type Playlist } from '../../entities/playlist'
import { Button } from '../../shared/ui/Button'

export function PlaylistManagement() {
  const [showForm, setShowForm] = useState(false)
  const [editingPlaylist, setEditingPlaylist] = useState<Playlist | null>(null)
  const [formData, setFormData] = useState({ name: '', description: '', isPublic: false })

  const [playlists, isLoading] = useUnit([$playlists, $playlistsLoading])
  const handleFetchPlaylists = useUnit(fetchPlaylists)
  const handleCreatePlaylist = useUnit(createPlaylist)
  const handleUpdatePlaylist = useUnit(updatePlaylist)
  const handleDeletePlaylist = useUnit(deletePlaylist)

  useEffect(() => {
    handleFetchPlaylists()
  }, [handleFetchPlaylists])

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    try {
      if (editingPlaylist) {
        await handleUpdatePlaylist({ id: editingPlaylist.id, data: formData })
      } else {
        await handleCreatePlaylist(formData)
      }
      setShowForm(false)
      setEditingPlaylist(null)
      setFormData({ name: '', description: '', isPublic: false })
    } catch (error) {
      console.error('Failed to save playlist:', error)
    }
  }

  const handleEdit = (playlist: Playlist) => {
    setEditingPlaylist(playlist)
    setFormData({ 
      name: playlist.name, 
      description: playlist.description || '', 
      isPublic: playlist.isPublic 
    })
    setShowForm(true)
  }

  const handleDelete = async (playlistId: number) => {
    if (window.confirm('Are you sure you want to delete this playlist?')) {
      try {
        await handleDeletePlaylist(playlistId)
      } catch (error) {
        console.error('Failed to delete playlist:', error)
      }
    }
  }

  if (showForm) {
    return (
      <div className="bg-white rounded-lg shadow-md p-6">
        <h2 className="text-xl font-semibold text-gray-900 mb-4">
          {editingPlaylist ? 'Edit Playlist' : 'Create New Playlist'}
        </h2>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700">Playlist Name *</label>
            <input
              type="text"
              value={formData.name}
              onChange={(e) => setFormData(prev => ({ ...prev, name: e.target.value }))}
              required
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700">Description</label>
            <textarea
              value={formData.description}
              onChange={(e) => setFormData(prev => ({ ...prev, description: e.target.value }))}
              rows={3}
              className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
          <div>
            <label className="flex items-center">
              <input
                type="checkbox"
                checked={formData.isPublic}
                onChange={(e) => setFormData(prev => ({ ...prev, isPublic: e.target.checked }))}
                className="mr-2"
              />
              Public Playlist
            </label>
          </div>
          <div className="flex justify-end space-x-3">
            <Button type="button" variant="outline" onClick={() => setShowForm(false)}>
              Cancel
            </Button>
            <Button type="submit">
              {editingPlaylist ? 'Update' : 'Create'} Playlist
            </Button>
          </div>
        </form>
      </div>
    )
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-xl font-semibold text-gray-900">Playlist Management</h2>
        <Button onClick={() => setShowForm(true)}>Add New Playlist</Button>
      </div>

      {isLoading ? (
        <div className="text-center py-8">Loading playlists...</div>
      ) : playlists.length === 0 ? (
        <div className="text-center py-8">No playlists found</div>
      ) : (
        <div className="bg-white shadow-md rounded-lg overflow-hidden">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Owner</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Visibility</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {playlists.map((playlist) => (
                <tr key={playlist.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                    {playlist.name}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-gray-500">
                    {playlist.owner.username}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`px-2 py-1 text-xs rounded-full ${
                      playlist.isPublic ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                    }`}>
                      {playlist.isPublic ? 'Public' : 'Private'}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-gray-500">
                    {new Date(playlist.createdAt).toLocaleDateString()}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap space-x-2">
                    <Button size="sm" variant="outline" onClick={() => handleEdit(playlist)}>
                      Edit
                    </Button>
                    <Button 
                      size="sm" 
                      variant="outline"
                      onClick={() => handleDelete(playlist.id)}
                      className="text-red-600 hover:text-red-900 hover:bg-red-50"
                    >
                      Delete
                    </Button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  )
}