import { useEffect } from 'react'
import { useUnit } from 'effector-react'
import { $tracks, $tracksLoading, fetchTracks } from '../../entities/track'
import { Header } from '../../widgets/header'

export function TrackListPage() {
  const [tracks, isLoading] = useUnit([$tracks, $tracksLoading])
  const handleFetchTracks = useUnit(fetchTracks)

  useEffect(() => {
    handleFetchTracks()
  }, [handleFetchTracks])

  return (
    <div>
      <Header />
      <main className="max-w-6xl mx-auto px-4 py-8">
        <h1 className="text-2xl font-bold text-gray-900 mb-6">Track Library</h1>
        
        {isLoading ? (
          <div className="text-center py-8">
            <p className="text-gray-500">Loading tracks...</p>
          </div>
        ) : tracks.length === 0 ? (
          <div className="text-center py-12">
            <p className="text-gray-500">No tracks available</p>
          </div>
        ) : (
          <div className="bg-white rounded-lg shadow-md overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full">
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