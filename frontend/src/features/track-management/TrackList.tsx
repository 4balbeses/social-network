import { useEffect } from 'react'
import { useUnit } from 'effector-react'
import { $tracks, $tracksLoading, fetchTracks, deleteTrack } from '../../entities/track'
import { Button } from '../../shared/ui/Button'

interface TrackListProps {
  onEdit?: (trackId: number) => void
  onView?: (trackId: number) => void
}

export function TrackList({ onEdit, onView }: TrackListProps) {
  const [tracks, isLoading] = useUnit([$tracks, $tracksLoading])
  const handleFetchTracks = useUnit(fetchTracks)
  const handleDeleteTrack = useUnit(deleteTrack)

  useEffect(() => {
    handleFetchTracks()
  }, [handleFetchTracks])

  const handleDelete = async (trackId: number) => {
    if (window.confirm('Are you sure you want to delete this track?')) {
      try {
        await handleDeleteTrack(trackId)
      } catch (error) {
        console.error('Failed to delete track:', error)
      }
    }
  }

  if (isLoading) {
    return (
      <div className="flex justify-center py-8">
        <div className="text-gray-500">Loading tracks...</div>
      </div>
    )
  }

  if (tracks.length === 0) {
    return (
      <div className="text-center py-8">
        <p className="text-gray-500">No tracks found</p>
      </div>
    )
  }

  return (
    <div className="bg-white shadow-md rounded-lg overflow-hidden">
      <table className="min-w-full divide-y divide-gray-200">
        <thead className="bg-gray-50">
          <tr>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Name
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Genre
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              File
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody className="bg-white divide-y divide-gray-200">
          {tracks.map((track) => (
            <tr key={track.id} className="hover:bg-gray-50">
              <td className="px-6 py-4 whitespace-nowrap">
                <div className="text-sm font-medium text-gray-900">{track.name}</div>
                {track.description && (
                  <div className="text-sm text-gray-500 truncate max-w-xs">
                    {track.description}
                  </div>
                )}
              </td>
              <td className="px-6 py-4 whitespace-nowrap">
                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  {track.genre.name}
                </span>
              </td>
              <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {track.trackFile.originalName}
              </td>
              <td className="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                {onView && (
                  <Button
                    size="sm"
                    variant="outline"
                    onClick={() => onView(track.id)}
                  >
                    View
                  </Button>
                )}
                {onEdit && (
                  <Button
                    size="sm"
                    variant="outline"
                    onClick={() => onEdit(track.id)}
                  >
                    Edit
                  </Button>
                )}
                <Button
                  size="sm"
                  variant="outline"
                  onClick={() => handleDelete(track.id)}
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
  )
}