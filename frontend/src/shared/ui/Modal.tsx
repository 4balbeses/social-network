import { type ReactNode, useEffect } from 'react';
import { cn } from '@/shared/lib/utils';

interface ModalProps {
  isOpen: boolean;
  onClose: () => void;
  children: ReactNode;
  title?: string;
  size?: 'sm' | 'md' | 'lg' | 'xl';
}

export const Modal = ({ 
  isOpen, 
  onClose, 
  children, 
  title,
  size = 'md' 
}: ModalProps) => {
  useEffect(() => {
    if (isOpen) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = 'unset';
    }

    return () => {
      document.body.style.overflow = 'unset';
    };
  }, [isOpen]);

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center">
      <div 
        className="fixed inset-0 bg-black/50"
        onClick={onClose}
      />
      <div 
        className={cn(
          'relative bg-card text-card-foreground rounded-lg shadow-lg max-h-[90vh] overflow-y-auto',
          {
            'max-w-sm': size === 'sm',
            'max-w-md': size === 'md',
            'max-w-lg': size === 'lg',
            'max-w-2xl': size === 'xl',
          }
        )}
      >
        {title && (
          <div className="flex items-center justify-between p-4 border-b border-border">
            <h2 className="text-lg font-semibold text-card-foreground">{title}</h2>
            <button
              onClick={onClose}
              className="text-muted-foreground hover:text-card-foreground transition-colors"
            >
              ×
            </button>
          </div>
        )}
        <div className="p-4">
          {children}
        </div>
      </div>
    </div>
  );
};