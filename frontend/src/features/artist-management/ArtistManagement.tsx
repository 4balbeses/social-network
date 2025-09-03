import { useState, useEffect } from 'react'
import { useUnit } from 'effector-react'
import { $artists, $artistsLoading, fetchArtists, createArtist, updateArtist, deleteArtist, type Artist } from '../../entities/artist'
import { Button } from '../../shared/ui/Button'

export function ArtistManagement() {
  const [showForm, setShowForm] = useState(false)
  const [editingArtist, setEditingArtist] = useState<Artist | null>(null)
  const [formData, setFormData] = useState({ fullName: '', description: '' })

  const [artists, isLoading] = useUnit([$artists, $artistsLoading])
  const handleFetchArtists = useUnit(fetchArtists)
  const handleCreateArtist = useUnit(createArtist)
  const handleUpdateArtist = useUnit(updateArtist)
  const handleDeleteArtist = useUnit(deleteArtist)

  useEffect(() => {
    handleFetchArtists()
  }, [handleFetchArtists])

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    try {
      if (editingArtist) {
        await handleUpdateArtist({ id: editingArtist.id, data: formData })
      } else {
        await handleCreateArtist(formData)
      }
      setShowForm(false)
      setEditingArtist(null)
      setFormData({ fullName: '', description: '' })
    } catch (error) {
      console.error('Failed to save artist:', error)
    }
  }

  const handleEdit = (artist: Artist) => {
    setEditingArtist(artist)
    setFormData({ fullName: artist.fullName, description: artist.description || '' })
    setShowForm(true)
  }

  const handleDelete = async (artistId: number) => {
    if (window.confirm('Are you sure you want to delete this artist?')) {
      try {
        await handleDeleteArtist(artistId)
      } catch (error) {
        console.error('Failed to delete artist:', error)
      }
    }
  }

  if (showForm) {
    return (
      <div className="bg-card rounded-lg shadow-md p-6">
        <h2 className="text-xl font-semibold text-card-foreground mb-4">
          {editingArtist ? 'Edit Artist' : 'Create New Artist'}
        </h2>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-muted-foreground">Artist Name *</label>
            <input
              type="text"
              value={formData.fullName}
              onChange={(e) => setFormData(prev => ({ ...prev, fullName: e.target.value }))}
              required
              className="mt-1 block w-full rounded-md border-input shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-muted-foreground">Description</label>
            <textarea
              value={formData.description}
              onChange={(e) => setFormData(prev => ({ ...prev, description: e.target.value }))}
              rows={3}
              className="mt-1 block w-full rounded-md border-input shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
          <div className="flex justify-end space-x-3">
            <Button type="button" variant="outline" onClick={() => setShowForm(false)}>
              Cancel
            </Button>
            <Button type="submit">
              {editingArtist ? 'Update' : 'Create'} Artist
            </Button>
          </div>
        </form>
      </div>
    )
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-xl font-semibold text-card-foreground">Artist Management</h2>
        <Button onClick={() => setShowForm(true)}>Add New Artist</Button>
      </div>

      {isLoading ? (
        <div className="text-center py-8">Loading artists...</div>
      ) : artists.length === 0 ? (
        <div className="text-center py-8">No artists found</div>
      ) : (
        <div className="bg-card shadow-md rounded-lg overflow-hidden">
          <table className="min-w-full divide-y divide-border">
            <thead className="bg-muted">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Name</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Description</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-border">
              {artists.map((artist) => (
                <tr key={artist.id} className="hover:bg-accent transition-colors">
                  <td className="px-6 py-4 whitespace-nowrap font-medium text-card-foreground">
                    {artist.fullName}
                  </td>
                  <td className="px-6 py-4 text-muted-foreground max-w-xs truncate">
                    {artist.description}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap space-x-2">
                    <Button size="sm" variant="outline" onClick={() => handleEdit(artist)}>
                      Edit
                    </Button>
                    <Button 
                      size="sm" 
                      variant="outline"
                      onClick={() => handleDelete(artist.id)}
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