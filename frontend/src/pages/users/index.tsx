import { useEffect, useState } from 'react';
import { useUnit } from 'effector-react';
import { type User, type CreateUserDto } from '@/shared/types';
import { Button, Modal } from '@/shared/ui';
import { userStore, UserCard, UserForm } from '@/entities/user';

export const UsersPage = () => {
  const [users, loading, error] = useUnit([
    userStore.$items,
    userStore.$loading,
    userStore.$error,
  ]);

  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [editingUser, setEditingUser] = useState<User | null>(null);

  useEffect(() => {
    userStore.fetchItems();
  }, []);

  const handleCreateUser = (userData: CreateUserDto | User) => {
    if ('password' in userData) {
      // This is CreateUserDto - convert it to the format expected by userStore
      const userToCreate = {
        username: userData.username,
        fullName: userData.fullName,
        registeredAt: new Date().toISOString(),
        roles: ['user']
      };
      userStore.createItem(userToCreate);
    }
    setIsCreateModalOpen(false);
  };

  const handleUpdateUser = (userData: CreateUserDto | User) => {
    if ('id' in userData) {
      // This is User
      userStore.updateItem(userData);
    }
    setEditingUser(null);
  };

  const handleDeleteUser = (id: number) => {
    if (confirm('Are you sure you want to delete this user?')) {
      userStore.deleteItem(id);
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
        <h1 className="text-2xl font-bold">Users</h1>
        <Button onClick={() => setIsCreateModalOpen(true)}>
          Create User
        </Button>
      </div>

      {loading && (!Array.isArray(users) || users.length === 0) ? (
        <div>Loading users...</div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {Array.isArray(users) && users.map((user: User) => (
            <UserCard
              key={user.id}
              user={user}
              onEdit={setEditingUser}
              onDelete={handleDeleteUser}
            />
          ))}
        </div>
      )}

      {Array.isArray(users) && users.length === 0 && !loading && (
        <div className="text-center text-muted-foreground py-8">
          No users found. Create your first user!
        </div>
      )}

      <Modal
        isOpen={isCreateModalOpen}
        onClose={() => setIsCreateModalOpen(false)}
        title="Create User"
      >
        <UserForm
          onSubmit={handleCreateUser}
          onCancel={() => setIsCreateModalOpen(false)}
          loading={loading}
        />
      </Modal>

      <Modal
        isOpen={!!editingUser}
        onClose={() => setEditingUser(null)}
        title="Edit User"
      >
        {editingUser && (
          <UserForm
            user={editingUser}
            onSubmit={handleUpdateUser}
            onCancel={() => setEditingUser(null)}
            loading={loading}
          />
        )}
      </Modal>
    </div>
  );
};