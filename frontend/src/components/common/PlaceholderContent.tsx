import React from 'react';
import { useTranslation } from 'react-i18next';

interface PlaceholderContentProps {
  title?: string;
  message?: string;
  icon?: React.ReactNode;
  actionText?: string;
  onAction?: () => void;
}

const PlaceholderContent: React.FC<PlaceholderContentProps> = ({
  title,
  message,
  icon,
  actionText,
  onAction,
}) => {
  const { t } = useTranslation();
  
  return (
    <div className="placeholder-content">
      {icon && <div className="placeholder-icon">{icon}</div>}
      
      <h2 className="text-xl font-semibold mb-2">
        {title || t('common.placeholder.title', 'Coming Soon')}
      </h2>
      
      <p className="text-gray-500 mb-4">
        {message || t('common.placeholder.message', 'This feature is currently under development and will be available soon.')}
      </p>
      
      {actionText && onAction && (
        <button 
          className="btn btn-primary"
          onClick={onAction}
        >
          {actionText}
        </button>
      )}
    </div>
  );
};

export default PlaceholderContent;