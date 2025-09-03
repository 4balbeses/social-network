import { useEffect } from 'react'
import { useUnit } from 'effector-react'
import { $tracks, $tracksLoading, fetchTracks } from '../../entities/track'

export const TracksPage = () => {
  const [tracks, isLoading] = useUnit([$tracks, $tracksLoading])
  const handleFetchTracks = useUnit(fetchTracks)

  useEffect(() => {
    handleFetchTracks()
  }, [handleFetchTracks])

  if (isLoading) {
    return (
      <div className="p-6">
        <h1 className="text-2xl font-bold mb-6">Tracks</h1>
        <div className="flex justify-center py-8">
          <div className="text-muted-foreground">Loading tracks...</div>
        </div>
      </div>
    )
  }

  return (
    <div className="p-6">
      <h1 className="text-2xl font-bold mb-6">Tracks</h1>
      
      {tracks.length === 0 ? (
        <div className="text-center py-8">
          <p className="text-muted-foreground">No tracks found</p>
          <p className="text-sm text-muted-foreground mt-2">Tracks will appear here when they are uploaded</p>
        </div>
      ) : (
        <div className="bg-card shadow-md rounded-lg overflow-hidden">
          <table className="min-w-full divide-y divide-border">
            <thead className="bg-muted">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                  Name
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                  Genre
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                  File
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                  Description
                </th>
              </tr>
            </thead>
            <tbody className="bg-card divide-y divide-border">
              {tracks.map((track) => (
                <tr key={track.id} className="hover:bg-accent transition-colors">
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="text-sm font-medium text-card-foreground">{track.name}</div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      {track.genre?.name || 'Unknown'}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                    {track.trackFile?.originalName || 'No file'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                    {track.description || '-'}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
};