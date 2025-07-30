import React from 'react';
import { useTranslation } from 'react-i18next';

interface ErrorMessageProps {
  title?: string;
  message?: string;
  onRetry?: () => void;
  retryText?: string;
}

const ErrorMessage: React.FC<ErrorMessageProps> = ({
  title,
  message,
  onRetry,
  retryText,
}) => {
  const { t } = useTranslation();
  
  return (
    <div className="p-4 border border-danger-200 rounded-md bg-danger-50">
      <div className="flex">
        <div className="flex-shrink-0">
          <svg 
            className="w-5 h-5 text-danger-400" 
            xmlns="http://www.w3.org/2000/svg" 
            viewBox="0 0 20 20" 
            fill="currentColor" 
            aria-hidden="true"
          >
            <path 
              fillRule="evenodd" 
              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" 
              clipRule="evenodd" 
            />
          </svg>
        </div>
        <div className="ml-3">
          <h3 className="text-sm font-medium text-danger-800">
            {title || t('common.error.title', 'Error')}
          </h3>
          <div className="mt-2 text-sm text-danger-700">
            <p>{message || t('common.error.message', 'An error occurred. Please try again.')}</p>
          </div>
          {onRetry && (
            <div className="mt-4">
              <button
                type="button"
                className="inline-flex items-center px-3 py-2 text-sm font-medium text-danger-800 bg-danger-100 border border-transparent rounded-md hover:bg-danger-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-danger-500"
                onClick={onRetry}
              >
                {retryText || t('common.error.retry', 'Try Again')}
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default ErrorMessage;