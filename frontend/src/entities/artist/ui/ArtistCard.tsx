import { type Artist } from '@/shared/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/shared/ui';

interface ArtistCardProps {
  artist: Artist;
  onEdit?: (artist: Artist) => void;
  onDelete?: (id: number) => void;
}

export const ArtistCard = ({ artist, onEdit, onDelete }: ArtistCardProps) => {
  return (
    <Card>
      <CardHeader>
        <CardTitle>{artist.fullName}</CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-2">
          {artist.description && (
            <p className="text-sm text-gray-600">{artist.description}</p>
          )}
        </div>
        {(onEdit || onDelete) && (
          <div className="flex gap-2 mt-4">
            {onEdit && (
              <button
                onClick={() => onEdit(artist)}
                className="text-sm text-blue-600 hover:text-blue-800"
              >
                Edit
              </button>
            )}
            {onDelete && (
              <button
                onClick={() => onDelete(artist.id)}
                className="text-sm text-red-600 hover:text-red-800"
              >
                Delete
              </button>
            )}
          </div>
        )}
      </CardContent>
    </Card>
  );
};