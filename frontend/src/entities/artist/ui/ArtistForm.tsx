import { useState } from 'react';
import { type Artist, type CreateArtistDto } from '@/shared/types';
import { Button, Input } from '@/shared/ui';

interface ArtistFormProps {
  artist?: Artist;
  onSubmit: (data: CreateArtistDto | Artist) => void;
  onCancel: () => void;
  loading?: boolean;
}

export const ArtistForm = ({ artist, onSubmit, onCancel, loading }: ArtistFormProps) => {
  const [formData, setFormData] = useState({
    fullName: artist?.fullName || '',
    description: artist?.description || '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    if (artist) {
      onSubmit({ ...artist, ...formData });
    } else {
      onSubmit(formData as CreateArtistDto);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <Input
        label="Full Name"
        value={formData.fullName}
        onChange={(e) => setFormData({ ...formData, fullName: e.target.value })}
        required
      />
      <div className="space-y-1">
        <label className="text-sm font-medium text-gray-700">Description</label>
        <textarea
          className="input min-h-[80px] resize-y"
          value={formData.description}
          onChange={(e) => setFormData({ ...formData, description: e.target.value })}
          placeholder="Optional description"
        />
      </div>
      <div className="flex gap-2">
        <Button type="submit" disabled={loading}>
          {loading ? 'Saving...' : artist ? 'Update Artist' : 'Create Artist'}
        </Button>
        <Button type="button" variant="secondary" onClick={onCancel}>
          Cancel
        </Button>
      </div>
    </form>
  );
};