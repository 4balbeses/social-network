import { type Artist } from '@/shared/types';
import { Button } from '@/shared/ui/Button';

interface ArtistCardProps {
  artist: Artist;
  onEdit?: (artist: Artist) => void;
  onDelete?: (id: number) => void;
}

export const ArtistCard = ({ artist, onEdit, onDelete }: ArtistCardProps) => {
  return (
    <div className="group relative animate-scale-in">
      <div className="floating-card h-full p-6">
        {/* Artist Avatar/Icon */}
        <div className="mb-6">
          <div className="flex items-center space-x-4">
            <div className="w-20 h-20 bg-gradient-to-br from-primary-500 via-primary-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-2xl group-hover:scale-125 group-hover:rotate-6 transition-all duration-500">
              <span className="text-3xl">🎤</span>
            </div>
            <div className="flex-1">
              <h3 className="text-2xl font-bold mb-2 group-hover:bg-gradient-to-r group-hover:from-primary-600 group-hover:to-purple-500 group-hover:bg-clip-text group-hover:text-transparent transition-all duration-300">
                {artist.fullName}
              </h3>
              <div className="flex items-center text-sm">
                <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-primary-500/20 to-purple-500/20 text-primary-700 dark:text-primary-300 border border-primary-500/20 backdrop-blur-sm">
                  🎵 Artist
                </span>
              </div>
            </div>
          </div>
        </div>
        
        <div className="space-y-6">
          {artist.description ? (
            <p className="text-muted-foreground leading-relaxed line-clamp-3 text-lg">
              {artist.description}
            </p>
          ) : (
            <p className="text-muted-foreground italic text-lg">
              No description available
            </p>
          )}
          
          {/* Action buttons */}
          {(onEdit || onDelete) && (
            <div className="flex gap-3 pt-4 border-t border-border/30">
              {onEdit && (
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => onEdit(artist)}
                  className="flex-1 smooth-bounce"
                >
                  <span className="mr-2">✏️</span>
                  Edit
                </Button>
              )}
              {onDelete && (
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => onDelete(artist.id)}
                  className="flex-1 hover:bg-destructive/10 hover:text-destructive hover:border-destructive/30 smooth-bounce"
                >
                  <span className="mr-2">🗑️</span>
                  Delete
                </Button>
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  );
};