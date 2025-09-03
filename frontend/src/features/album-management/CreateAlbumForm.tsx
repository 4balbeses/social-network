import { useState, useEffect } from 'react'
import { useUnit } from 'effector-react'
import { createAlbum } from '../../entities/album'
import { fetchArtists, $artists } from '../../entities/artist'
import { Button } from '../../shared/ui/Button'

interface CreateAlbumFormProps {
  onSuccess?: () => void
  onCancel?: () => void
}

export function CreateAlbumForm({ onSuccess, onCancel }: CreateAlbumFormProps) {
  const [formData, setFormData] = useState({
    name: '',
    description: '',
    artistId: ''
  })

  const [artists] = useUnit([$artists])
  const handleCreateAlbum = useUnit(createAlbum)
  const handleFetchArtists = useUnit(fetchArtists)

  useEffect(() => {
    handleFetchArtists()
  }, [handleFetchArtists])

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    
    if (!formData.name || !formData.artistId) return

    try {
      await handleCreateAlbum({
        name: formData.name,
        description: formData.description || undefined,
        artistId: Number(formData.artistId)
      })
      onSuccess?.()
    } catch (error) {
      console.error('Failed to create album:', error)
    }
  }

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target
    setFormData(prev => ({ ...prev, [name]: value }))
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div>
        <label htmlFor="name" className="block text-sm font-medium text-muted-foreground">
          Album Name *
        </label>
        <input
          type="text"
          id="name"
          name="name"
          value={formData.name}
          onChange={handleChange}
          required
          className="mt-1 block w-full rounded-md border-input shadow-sm focus:border-blue-500 focus:ring-blue-500"
        />
      </div>

      <div>
        <label htmlFor="description" className="block text-sm font-medium text-muted-foreground">
          Description
        </label>
        <textarea
          id="description"
          name="description"
          value={formData.description}
          onChange={handleChange}
          rows={3}
          className="mt-1 block w-full rounded-md border-input shadow-sm focus:border-blue-500 focus:ring-blue-500"
        />
      </div>

      <div>
        <label htmlFor="artistId" className="block text-sm font-medium text-muted-foreground">
          Artist *
        </label>
        <select
          id="artistId"
          name="artistId"
          value={formData.artistId}
          onChange={handleChange}
          required
          className="mt-1 block w-full rounded-md border-input shadow-sm focus:border-blue-500 focus:ring-blue-500"
        >
          <option value="">Select an artist</option>
          {artists.map(artist => (
            <option key={artist.id} value={artist.id}>
              {artist.fullName}
            </option>
          ))}
        </select>
      </div>

      <div className="flex justify-end space-x-3">
        {onCancel && (
          <Button type="button" variant="outline" onClick={onCancel}>
            Cancel
          </Button>
        )}
        <Button type="submit">
          Create Album
        </Button>
      </div>
    </form>
  )
}