import { useEffect } from 'react'
import { useUnit } from 'effector-react'
import { $albums, $albumsLoading, fetchAlbums } from '../../entities/album'

export const AlbumsPage = () => {
  const [albums, isLoading] = useUnit([$albums, $albumsLoading])
  const handleFetchAlbums = useUnit(fetchAlbums)

  useEffect(() => {
    handleFetchAlbums()
  }, [handleFetchAlbums])

  if (isLoading) {
    return (
      <div className="p-6">
        <h1 className="text-2xl font-bold mb-6">Albums</h1>
        <div className="flex justify-center py-8">
          <div className="text-muted-foreground">Loading albums...</div>
        </div>
      </div>
    )
  }

  return (
    <div className="p-6">
      <h1 className="text-2xl font-bold mb-6">Albums</h1>
      
      {albums.length === 0 ? (
        <div className="text-center py-8">
          <p className="text-muted-foreground">No albums found</p>
          <p className="text-sm text-muted-foreground mt-2">Albums will appear here when they are created</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {albums.map((album) => (
            <div key={album.id} className="bg-card rounded-lg shadow-md p-6">
              <h3 className="text-lg font-semibold text-card-foreground mb-2">{album.name}</h3>
              {album.description && (
                <p className="text-muted-foreground mb-3">{album.description}</p>
              )}
              <div className="text-sm text-muted-foreground">
                <p>Artist: {album.artist?.fullName || 'Unknown'}</p>
                <p>Created: {new Date(album.createdAt).toLocaleDateString()}</p>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};