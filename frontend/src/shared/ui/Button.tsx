import { type ButtonHTMLAttributes, type ReactNode } from 'react';
import { cn } from '@/shared/lib/utils';

interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'outline';
  size?: 'sm' | 'md' | 'lg';
  children: ReactNode;
}

export const Button = ({
  variant = 'primary',
  size = 'md',
  className,
  children,
  ...props
}: ButtonProps) => {
  return (
    <button
      className={cn(
        'btn',
        {
          'btn-primary': variant === 'primary',
          'btn-secondary': variant === 'secondary',
          'btn-outline': variant === 'outline',
          'h-8 px-3 text-xs': size === 'sm',
          'h-9 px-4 py-2': size === 'md',
          'h-10 px-8': size === 'lg',
        },
        className
      )}
      {...props}
    >
      {children}
    </button>
  );
};