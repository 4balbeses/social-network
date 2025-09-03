import { type User } from '@/shared/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/shared/ui';
import { Button } from '@/shared/ui/Button';

interface UserCardProps {
  user: User;
  onEdit?: (user: User) => void;
  onDelete?: (id: number) => void;
}

export const UserCard = ({ user, onEdit, onDelete }: UserCardProps) => {
  const getRoleColor = (roles: string[]) => {
    if (roles.includes('admin')) return 'from-red-500 to-red-600';
    if (roles.includes('moderator')) return 'from-orange-500 to-orange-600';
    return 'from-primary-500 to-primary-600';
  };

  const getRoleBadgeColor = (role: string) => {
    switch (role.toLowerCase()) {
      case 'admin':
        return 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900 dark:text-red-200 dark:border-red-700';
      case 'moderator':
        return 'bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-900 dark:text-orange-200 dark:border-orange-700';
      default:
        return 'bg-primary-100 text-primary-800 border-primary-200 dark:bg-primary-900 dark:text-primary-200 dark:border-primary-700';
    }
  };

  return (
    <div className="group relative">
      <Card className="h-full transition-all duration-300 hover:shadow-xl hover:-translate-y-2 border-border/50 hover:border-primary-300 bg-gradient-to-br from-card to-card/90">
        {/* User Avatar/Icon */}
        <CardHeader className="pb-4">
          <div className="flex items-center space-x-4">
            <div className={`w-16 h-16 bg-gradient-to-br ${getRoleColor(user.roles)} rounded-full flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300`}>
              <span className="text-2xl text-white">👤</span>
            </div>
            <div className="flex-1">
              <CardTitle className="text-xl mb-1 group-hover:text-primary-600 transition-colors">
                {user.fullName}
              </CardTitle>
              <p className="text-sm text-muted-foreground font-mono">
                @{user.username}
              </p>
            </div>
          </div>
        </CardHeader>
        
        <CardContent className="pt-0">
          <div className="space-y-4">
            {/* User Info */}
            <div className="space-y-3">
              <div className="flex items-center space-x-2 text-sm">
                <span className="text-muted-foreground">📅</span>
                <span className="text-muted-foreground">Joined:</span>
                <span className="font-medium text-card-foreground">
                  {new Date(user.registeredAt).toLocaleDateString()}
                </span>
              </div>
              
              {/* Roles */}
              <div className="flex items-start space-x-2">
                <span className="text-sm text-muted-foreground mt-1">🏷️ Roles:</span>
                <div className="flex flex-wrap gap-1">
                  {user.roles.map((role, index) => (
                    <span
                      key={index}
                      className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ${getRoleBadgeColor(role)}`}
                    >
                      {role}
                    </span>
                  ))}
                </div>
              </div>
            </div>
            
            {/* Action buttons */}
            {(onEdit || onDelete) && (
              <div className="flex gap-2 pt-2 border-t border-border">
                {onEdit && (
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => onEdit(user)}
                    className="flex-1 hover:bg-primary-50 hover:text-primary-700 hover:border-primary-300"
                  >
                    <span className="mr-2">✏️</span>
                    Edit
                  </Button>
                )}
                {onDelete && (
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => onDelete(user.id)}
                    className="flex-1 hover:bg-red-50 hover:text-red-700 hover:border-red-300"
                  >
                    <span className="mr-2">🗑️</span>
                    Delete
                  </Button>
                )}
              </div>
            )}
          </div>
        </CardContent>
        
        {/* Hover overlay effect */}
        <div className="absolute inset-0 bg-gradient-to-r from-primary-500/5 to-accent/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-xl pointer-events-none" />
      </Card>
    </div>
  );
};