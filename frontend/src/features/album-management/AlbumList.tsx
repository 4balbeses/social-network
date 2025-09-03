import { useEffect } from 'react'
import { useUnit } from 'effector-react'
import { $albums, $albumsLoading, fetchAlbums, deleteAlbum } from '../../entities/album'
import { Button } from '../../shared/ui/Button'

interface AlbumListProps {
  onEdit?: (albumId: number) => void
  onView?: (albumId: number) => void
}

export function AlbumList({ onEdit, onView }: AlbumListProps) {
  const [albums, isLoading] = useUnit([$albums, $albumsLoading])
  const handleFetchAlbums = useUnit(fetchAlbums)
  const handleDeleteAlbum = useUnit(deleteAlbum)

  useEffect(() => {
    handleFetchAlbums()
  }, [handleFetchAlbums])

  const handleDelete = async (albumId: number) => {
    if (window.confirm('Are you sure you want to delete this album?')) {
      try {
        await handleDeleteAlbum(albumId)
      } catch (error) {
        console.error('Failed to delete album:', error)
      }
    }
  }

  if (isLoading) {
    return (
      <div className="flex justify-center py-8">
        <div className="text-muted-foreground">Loading albums...</div>
      </div>
    )
  }

  if (albums.length === 0) {
    return (
      <div className="text-center py-8">
        <p className="text-muted-foreground">No albums found</p>
      </div>
    )
  }

  return (
    <div className="bg-card shadow-md rounded-lg overflow-hidden">
      <table className="min-w-full divide-y divide-border">
        <thead className="bg-muted">
          <tr>
            <th className="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
              Name
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
              Artist
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
              Created
            </th>
            <th className="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody className="bg-card divide-y divide-border">
          {albums.map((album) => (
            <tr key={album.id} className="hover:bg-accent transition-colors">
              <td className="px-6 py-4 whitespace-nowrap">
                <div className="text-sm font-medium text-card-foreground">{album.name}</div>
                {album.description && (
                  <div className="text-sm text-muted-foreground truncate max-w-xs">
                    {album.description}
                  </div>
                )}
              </td>
              <td className="px-6 py-4 whitespace-nowrap">
                <div className="text-sm text-card-foreground">{album.artist.fullName}</div>
              </td>
              <td className="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                {new Date(album.createdAt).toLocaleDateString()}
              </td>
              <td className="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                {onView && (
                  <Button
                    size="sm"
                    variant="outline"
                    onClick={() => onView(album.id)}
                  >
                    View
                  </Button>
                )}
                {onEdit && (
                  <Button
                    size="sm"
                    variant="outline"
                    onClick={() => onEdit(album.id)}
                  >
                    Edit
                  </Button>
                )}
                <Button
                  size="sm"
                  variant="outline"
                  onClick={() => handleDelete(album.id)}
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