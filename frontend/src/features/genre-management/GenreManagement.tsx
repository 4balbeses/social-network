import { useState, useEffect } from 'react'
import { useUnit } from 'effector-react'
import { $genres, $genresLoading, fetchGenres, createGenre, updateGenre, deleteGenre, type Genre } from '../../entities/genre'
import { Button } from '../../shared/ui/Button'

export function GenreManagement() {
  const [showForm, setShowForm] = useState(false)
  const [editingGenre, setEditingGenre] = useState<Genre | null>(null)
  const [formData, setFormData] = useState({ name: '', description: '' })

  const [genres, isLoading] = useUnit([$genres, $genresLoading])
  const handleFetchGenres = useUnit(fetchGenres)
  const handleCreateGenre = useUnit(createGenre)
  const handleUpdateGenre = useUnit(updateGenre)
  const handleDeleteGenre = useUnit(deleteGenre)

  useEffect(() => {
    handleFetchGenres()
  }, [handleFetchGenres])

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    try {
      if (editingGenre) {
        await handleUpdateGenre({ id: editingGenre.id, data: formData })
      } else {
        await handleCreateGenre(formData)
      }
      setShowForm(false)
      setEditingGenre(null)
      setFormData({ name: '', description: '' })
    } catch (error) {
      console.error('Failed to save genre:', error)
    }
  }

  const handleEdit = (genre: Genre) => {
    setEditingGenre(genre)
    setFormData({ name: genre.name, description: genre.description || '' })
    setShowForm(true)
  }

  const handleDelete = async (genreId: number) => {
    if (window.confirm('Are you sure you want to delete this genre?')) {
      try {
        await handleDeleteGenre(genreId)
      } catch (error) {
        console.error('Failed to delete genre:', error)
      }
    }
  }

  if (showForm) {
    return (
      <div className="bg-white rounded-lg shadow-md p-6">
        <h2 className="text-xl font-semibold text-gray-900 mb-4">
          {editingGenre ? 'Edit Genre' : 'Create New Genre'}
        </h2>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700">Genre Name *</label>
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
          <div className="flex justify-end space-x-3">
            <Button type="button" variant="outline" onClick={() => setShowForm(false)}>
              Cancel
            </Button>
            <Button type="submit">
              {editingGenre ? 'Update' : 'Create'} Genre
            </Button>
          </div>
        </form>
      </div>
    )
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-xl font-semibold text-gray-900">Genre Management</h2>
        <Button onClick={() => setShowForm(true)}>Add New Genre</Button>
      </div>

      {isLoading ? (
        <div className="text-center py-8">Loading genres...</div>
      ) : genres.length === 0 ? (
        <div className="text-center py-8">No genres found</div>
      ) : (
        <div className="bg-white shadow-md rounded-lg overflow-hidden">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {genres.map((genre) => (
                <tr key={genre.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                    {genre.name}
                  </td>
                  <td className="px-6 py-4 text-gray-500 max-w-xs truncate">
                    {genre.description}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap space-x-2">
                    <Button size="sm" variant="outline" onClick={() => handleEdit(genre)}>
                      Edit
                    </Button>
                    <Button 
                      size="sm" 
                      variant="outline"
                      onClick={() => handleDelete(genre.id)}
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