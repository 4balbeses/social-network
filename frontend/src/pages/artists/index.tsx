import { useEffect, useState } from 'react';
import { useUnit } from 'effector-react';
import { type Artist, type CreateArtistDto } from '@/shared/types';
import { Button, Modal } from '@/shared/ui';
import { artistStore, ArtistCard, ArtistForm } from '@/entities/artist';

export const ArtistsPage = () => {
  const [artists, loading, error] = useUnit([
    artistStore.$items,
    artistStore.$loading,
    artistStore.$error,
  ]);

  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [editingArtist, setEditingArtist] = useState<Artist | null>(null);

  useEffect(() => {
    artistStore.fetchItems();
  }, []);

  const handleCreateArtist = (artistData: CreateArtistDto) => {
    artistStore.createItem(artistData);
    setIsCreateModalOpen(false);
  };

  const handleUpdateArtist = (artistData: Artist | CreateArtistDto) => {
    if ('id' in artistData) {
      artistStore.updateItem(artistData);
    }
    setEditingArtist(null);
  };

  const handleDeleteArtist = (id: number) => {
    if (confirm('Are you sure you want to delete this artist?')) {
      artistStore.deleteItem(id);
    }
  };

  if (error) {
    return (
      <div className="p-6">
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
          Error: {error}
        </div>
      </div>
    );
  }

  return (
    <div className="p-6">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold">Artists</h1>
        <Button onClick={() => setIsCreateModalOpen(true)}>
          Create Artist
        </Button>
      </div>

      {loading && artists.length === 0 ? (
        <div>Loading artists...</div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {artists.map((artist: Artist) => (
            <ArtistCard
              key={artist.id}
              artist={artist}
              onEdit={setEditingArtist}
              onDelete={handleDeleteArtist}
            />
          ))}
        </div>
      )}

      {artists.length === 0 && !loading && (
        <div className="text-center text-gray-500 py-8">
          No artists found. Create your first artist!
        </div>
      )}

      <Modal
        isOpen={isCreateModalOpen}
        onClose={() => setIsCreateModalOpen(false)}
        title="Create Artist"
      >
        <ArtistForm
          onSubmit={handleCreateArtist}
          onCancel={() => setIsCreateModalOpen(false)}
          loading={loading}
        />
      </Modal>

      <Modal
        isOpen={!!editingArtist}
        onClose={() => setEditingArtist(null)}
        title="Edit Artist"
      >
        {editingArtist && (
          <ArtistForm
            artist={editingArtist}
            onSubmit={handleUpdateArtist}
            onCancel={() => setEditingArtist(null)}
            loading={loading}
          />
        )}
      </Modal>
    </div>
  );
};