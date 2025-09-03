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
      <div className="flex justify-center items-center py-16">
        <div className="flex flex-col items-center space-y-4">
          <div className="w-8 h-8 border-4 border-primary-200 border-t-primary-600 rounded-full animate-spin"></div>
          <div className="text-muted-foreground font-medium">Loading tracks...</div>
        </div>
      </div>
    )
  }

  if (tracks.length === 0) {
    return (
      <div className="text-center py-16">
        <div className="flex flex-col items-center space-y-4">
          <div className="w-16 h-16 bg-muted rounded-full flex items-center justify-center">
            <span className="text-2xl">🎵</span>
          </div>
          <div>
            <p className="text-lg font-medium text-card-foreground mb-2">No tracks found</p>
            <p className="text-muted-foreground">Start by adding your first track to the collection.</p>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="card overflow-hidden">
      {/* Header */}
      <div className="px-6 py-4 border-b border-border bg-muted/20">
        <h3 className="text-lg font-semibold text-card-foreground">Tracks ({tracks.length})</h3>
      </div>
      
      <div className="overflow-x-auto">
        <table className="min-w-full divide-y divide-border/50">
          <thead>
            <tr className="bg-gradient-to-r from-muted/30 to-muted/10">
              <th className="px-6 py-4 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                <div className="flex items-center space-x-1">
                  <span>🎵</span>
                  <span>Track</span>
                </div>
              </th>
              <th className="px-6 py-4 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                <div className="flex items-center space-x-1">
                  <span>🏷️</span>
                  <span>Genre</span>
                </div>
              </th>
              <th className="px-6 py-4 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                <div className="flex items-center space-x-1">
                  <span>📁</span>
                  <span>File</span>
                </div>
              </th>
              <th className="px-6 py-4 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody className="bg-card divide-y divide-border/30">
            {tracks.map((track, index) => (
              <tr 
                key={track.id} 
                className={`group hover:bg-gradient-to-r hover:from-primary-50/50 hover:to-accent/20 transition-all duration-200 ${
                  index % 2 === 0 ? 'bg-card' : 'bg-muted/10'
                }`}
              >
                <td className="px-6 py-5">
                  <div className="flex items-center space-x-3">
                    <div className="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-lg flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-200">
                      <span className="text-white text-sm">🎤</span>
                    </div>
                    <div>
                      <div className="text-sm font-semibold text-card-foreground group-hover:text-primary-700 transition-colors">
                        {track.name}
                      </div>
                      {track.description && (
                        <div className="text-xs text-muted-foreground truncate max-w-xs mt-1">
                          {track.description}
                        </div>
                      )}
                    </div>
                  </div>
                </td>
                <td className="px-6 py-5">
                  <span className="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-gradient-to-r from-primary-100 to-primary-200 text-primary-800 border border-primary-200 dark:from-primary-900 dark:to-primary-800 dark:text-primary-200 dark:border-primary-700">
                    {track.genre.name}
                  </span>
                </td>
                <td className="px-6 py-5">
                  <div className="flex items-center space-x-2">
                    <span className="text-sm text-muted-foreground">📎</span>
                    <span className="text-sm text-muted-foreground font-mono truncate max-w-[200px]">
                      {track.trackFile.originalName}
                    </span>
                  </div>
                </td>
                <td className="px-6 py-5">
                  <div className="flex items-center space-x-2">
                    {onView && (
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => onView(track.id)}
                        className="hover:bg-primary-50 hover:border-primary-300 hover:text-primary-700"
                      >
                        <span className="mr-1">👁️</span>
                        View
                      </Button>
                    )}
                    {onEdit && (
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => onEdit(track.id)}
                        className="hover:bg-info-50 hover:border-info-300 hover:text-info-700"
                      >
                        <span className="mr-1">✏️</span>
                        Edit
                      </Button>
                    )}
                    <Button
                      size="sm"
                      variant="outline"
                      onClick={() => handleDelete(track.id)}
                      className="hover:bg-red-50 hover:border-red-300 hover:text-red-700"
                    >
                      <span className="mr-1">🗑️</span>
                      Delete
                    </Button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}