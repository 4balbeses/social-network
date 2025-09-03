import { useEffect } from 'react'
import { useUnit } from 'effector-react'
import { $playlists, $playlistsLoading, fetchPlaylists } from '../../entities/playlist'

export const PlaylistsPage = () => {
  const [playlists, isLoading] = useUnit([$playlists, $playlistsLoading])
  const handleFetchPlaylists = useUnit(fetchPlaylists)

  useEffect(() => {
    handleFetchPlaylists()
  }, [handleFetchPlaylists])

  if (isLoading) {
    return (
      <div className="p-6">
        <h1 className="text-2xl font-bold mb-6">Playlists</h1>
        <div className="flex justify-center py-8">
          <div className="text-muted-foreground">Loading playlists...</div>
        </div>
      </div>
    )
  }

  return (
    <div className="p-6">
      <h1 className="text-2xl font-bold mb-6">Playlists</h1>
      
      {playlists.length === 0 ? (
        <div className="text-center py-8">
          <p className="text-muted-foreground">No playlists found</p>
          <p className="text-sm text-muted-foreground mt-2">Playlists will appear here when they are created</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {playlists.map((playlist) => (
            <div key={playlist.id} className="bg-card rounded-lg shadow-md p-6">
              <div className="flex justify-between items-start mb-3">
                <h3 className="text-lg font-semibold text-card-foreground">{playlist.name}</h3>
                <span className={`px-2 py-1 text-xs rounded-full ${
                  playlist.isPublic 
                    ? 'bg-green-100 text-green-800' 
                    : 'bg-muted text-muted-foreground'
                }`}>
                  {playlist.isPublic ? 'Public' : 'Private'}
                </span>
              </div>
              
              {playlist.description && (
                <p className="text-muted-foreground mb-3">{playlist.description}</p>
              )}
              
              <div className="text-sm text-muted-foreground">
                <p>Owner: {playlist.owner?.username || 'Unknown'}</p>
                <p>Created: {new Date(playlist.createdAt).toLocaleDateString()}</p>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};