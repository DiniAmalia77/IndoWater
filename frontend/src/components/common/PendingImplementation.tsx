import React from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';

interface PendingImplementationProps {
  title?: string;
  message?: string;
  backUrl?: string;
  backText?: string;
}

const PendingImplementation: React.FC<PendingImplementationProps> = ({
  title,
  message,
  backUrl = '/',
  backText,
}) => {
  const { t } = useTranslation();
  const navigate = useNavigate();
  
  return (
    <div className="flex flex-col items-center justify-center min-h-[60vh] p-6 text-center">
      <div className="w-full max-w-md">
        <div className="mb-6 text-primary-500">
          <svg 
            xmlns="http://www.w3.org/2000/svg" 
            className="w-16 h-16 mx-auto" 
            fill="none" 
            viewBox="0 0 24 24" 
            stroke="currentColor"
          >
            <path 
              strokeLinecap="round" 
              strokeLinejoin="round" 
              strokeWidth={2} 
              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" 
            />
          </svg>
        </div>
        
        <h2 className="mb-2 text-2xl font-bold">
          {title || t('common.pendingImplementation.title', 'Under Construction')}
        </h2>
        
        <p className="mb-6 text-gray-500">
          {message || t('common.pendingImplementation.message', 'This page is currently under development. Please check back later.')}
        </p>
        
        <button 
          className="btn btn-primary"
          onClick={() => navigate(backUrl)}
        >
          {backText || t('common.pendingImplementation.back', 'Go Back')}
        </button>
      </div>
    </div>
  );
};

export default PendingImplementation;