import { useEffect } from 'react'
import { useUnit } from 'effector-react'
import { $tracks, $tracksLoading, fetchTracks } from '../../entities/track'

export function TrackListPage() {
  const [tracks, isLoading] = useUnit([$tracks, $tracksLoading])
  const handleFetchTracks = useUnit(fetchTracks)

  useEffect(() => {
    handleFetchTracks()
  }, [handleFetchTracks])

  return (
    <div>
      <main className="max-w-6xl mx-auto px-4 py-8">
        <h1 className="text-2xl font-bold text-card-foreground mb-6">Track Library</h1>
        
        {isLoading ? (
          <div className="text-center py-8">
            <p className="text-muted-foreground">Loading tracks...</p>
          </div>
        ) : tracks.length === 0 ? (
          <div className="text-center py-12">
            <p className="text-muted-foreground">No tracks available</p>
          </div>
        ) : (
          <div className="bg-card rounded-lg shadow-md overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full">
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
                  </tr>
                </thead>
                <tbody className="bg-card divide-y divide-border">
                  {tracks.map((track) => (
                    <tr key={track.id} className="hover:bg-accent transition-colors">
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="text-sm font-medium text-card-foreground">{track.name}</div>
                        {track.description && (
                          <div className="text-sm text-muted-foreground truncate max-w-xs">
                            {track.description}
                          </div>
                        )}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                          {track.genre.name}
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                        {track.trackFile.originalName}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}
      </main>
    </div>
  )
}