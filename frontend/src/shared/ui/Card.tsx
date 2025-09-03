import { type ReactNode } from 'react';
import { cn } from '@/shared/lib/utils';

interface CardProps {
  children: ReactNode;
  className?: string;
}

interface CardHeaderProps {
  children: ReactNode;
  className?: string;
}

interface CardContentProps {
  children: ReactNode;
  className?: string;
}

export const Card = ({ children, className }: CardProps) => {
  return <div className={cn('card', className)}>{children}</div>;
};

export const CardHeader = ({ children, className }: CardHeaderProps) => {
  return (
    <div className={cn('flex flex-col space-y-1.5 p-6', className)}>
      {children}
    </div>
  );
};

export const CardTitle = ({ children, className }: CardHeaderProps) => {
  return (
    <h3 className={cn('text-lg font-semibold leading-none tracking-tight', className)}>
      {children}
    </h3>
  );
};

export const CardDescription = ({ children, className }: CardHeaderProps) => {
  return (
    <p className={cn('text-sm text-muted-foreground', className)}>
      {children}
    </p>
  );
};

export const CardContent = ({ children, className }: CardContentProps) => {
  return <div className={cn('p-6 pt-0', className)}>{children}</div>;
};