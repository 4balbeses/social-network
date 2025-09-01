import { useState } from 'react';
import { type User, type CreateUserDto } from '@/shared/types';
import { Button, Input } from '@/shared/ui';

interface UserFormProps {
  user?: User;
  onSubmit: (data: CreateUserDto | User) => void;
  onCancel: () => void;
  loading?: boolean;
}

export const UserForm = ({ user, onSubmit, onCancel, loading }: UserFormProps) => {
  const [formData, setFormData] = useState({
    username: user?.username || '',
    fullName: user?.fullName || '',
    password: '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    if (user) {
      onSubmit({ ...user, ...formData });
    } else {
      onSubmit(formData as CreateUserDto);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <Input
        label="Username"
        value={formData.username}
        onChange={(e) => setFormData({ ...formData, username: e.target.value })}
        required
      />
      <Input
        label="Full Name"
        value={formData.fullName}
        onChange={(e) => setFormData({ ...formData, fullName: e.target.value })}
        required
      />
      {!user && (
        <Input
          label="Password"
          type="password"
          value={formData.password}
          onChange={(e) => setFormData({ ...formData, password: e.target.value })}
          required
        />
      )}
      <div className="flex gap-2">
        <Button type="submit" disabled={loading}>
          {loading ? 'Saving...' : user ? 'Update User' : 'Create User'}
        </Button>
        <Button type="button" variant="secondary" onClick={onCancel}>
          Cancel
        </Button>
      </div>
    </form>
  );
};