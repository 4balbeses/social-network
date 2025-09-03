import { useState, useEffect } from 'react'
import { useUnit } from 'effector-react'
import { $currentTrack, fetchTrack, updateTrack } from '../../entities/track'
import { fetchGenres, $genres } from '../../entities/genre'
import { fetchMedia, $mediaFiles } from '../../entities/media'
import { Button } from '../../shared/ui/Button'

interface EditTrackFormProps {
  trackId: number
  onSuccess?: () => void
  onCancel?: () => void
}

export function EditTrackForm({ trackId, onSuccess, onCancel }: EditTrackFormProps) {
  const [formData, setFormData] = useState({
    name: '',
    description: '',
    trackFileId: '',
    genreId: ''
  })

  const [currentTrack, genres, mediaFiles] = useUnit([$currentTrack, $genres, $mediaFiles])
  const handleUpdateTrack = useUnit(updateTrack)
  const handleFetchTrack = useUnit(fetchTrack)
  const handleFetchGenres = useUnit(fetchGenres)
  const handleFetchMedia = useUnit(fetchMedia)

  useEffect(() => {
    handleFetchTrack(trackId)
    handleFetchGenres()
    handleFetchMedia()
  }, [trackId, handleFetchTrack, handleFetchGenres, handleFetchMedia])

  useEffect(() => {
    if (currentTrack) {
      setFormData({
        name: currentTrack.name,
        description: currentTrack.description || '',
        trackFileId: currentTrack.trackFile.id.toString(),
        genreId: currentTrack.genre.id.toString()
      })
    }
  }, [currentTrack])

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    
    if (!formData.name || !formData.trackFileId || !formData.genreId) return

    try {
      await handleUpdateTrack({
        id: trackId,
        data: {
          name: formData.name,
          description: formData.description || undefined,
          trackFileId: Number(formData.trackFileId),
          genreId: Number(formData.genreId)
        }
      })
      onSuccess?.()
    } catch (error) {
      console.error('Failed to update track:', error)
    }
  }

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target
    setFormData(prev => ({ ...prev, [name]: value }))
  }

  if (!currentTrack) {
    return <div className="text-muted-foreground">Loading...</div>
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div>
        <label htmlFor="name" className="block text-sm font-medium text-muted-foreground">
          Track Name *
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
        <label htmlFor="trackFileId" className="block text-sm font-medium text-muted-foreground">
          Track File *
        </label>
        <select
          id="trackFileId"
          name="trackFileId"
          value={formData.trackFileId}
          onChange={handleChange}
          required
          className="mt-1 block w-full rounded-md border-input shadow-sm focus:border-blue-500 focus:ring-blue-500"
        >
          <option value="">Select a file</option>
          {mediaFiles.map(file => (
            <option key={file.id} value={file.id}>
              {file.originalName}
            </option>
          ))}
        </select>
      </div>

      <div>
        <label htmlFor="genreId" className="block text-sm font-medium text-muted-foreground">
          Genre *
        </label>
        <select
          id="genreId"
          name="genreId"
          value={formData.genreId}
          onChange={handleChange}
          required
          className="mt-1 block w-full rounded-md border-input shadow-sm focus:border-blue-500 focus:ring-blue-500"
        >
          <option value="">Select a genre</option>
          {genres.map(genre => (
            <option key={genre.id} value={genre.id}>
              {genre.name}
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
          Update Track
        </Button>
      </div>
    </form>
  )
}