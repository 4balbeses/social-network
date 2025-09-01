import { type User } from '@/shared/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/shared/ui';

interface UserCardProps {
  user: User;
  onEdit?: (user: User) => void;
  onDelete?: (id: number) => void;
}

export const UserCard = ({ user, onEdit, onDelete }: UserCardProps) => {
  return (
    <Card>
      <CardHeader>
        <CardTitle>{user.fullName}</CardTitle>
        <p className="text-sm text-gray-500">@{user.username}</p>
      </CardHeader>
      <CardContent>
        <div className="space-y-2">
          <p className="text-sm">
            <span className="font-medium">Registered:</span>{' '}
            {new Date(user.registeredAt).toLocaleDateString()}
          </p>
          <p className="text-sm">
            <span className="font-medium">Roles:</span>{' '}
            {user.roles.join(', ')}
          </p>
        </div>
        {(onEdit || onDelete) && (
          <div className="flex gap-2 mt-4">
            {onEdit && (
              <button
                onClick={() => onEdit(user)}
                className="text-sm text-blue-600 hover:text-blue-800"
              >
                Edit
              </button>
            )}
            {onDelete && (
              <button
                onClick={() => onDelete(user.id)}
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