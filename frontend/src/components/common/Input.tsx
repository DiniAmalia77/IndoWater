import React from 'react';
import { ExclamationCircleIcon } from '@heroicons/react/24/outline';

export interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
  helperText?: string;
  leftIcon?: React.ReactNode;
  rightIcon?: React.ReactNode;
  containerClassName?: string;
}

const Input = React.forwardRef<HTMLInputElement, InputProps>(
  ({ 
    className, 
    label, 
    error, 
    helperText, 
    leftIcon, 
    rightIcon, 
    containerClassName,
    id,
    ...props 
  }, ref) => {
    const inputId = id || `input-${Math.random().toString(36).substr(2, 9)}`;

    return (
      <div className={containerClassName}>
        {label && (
          <label 
            htmlFor={inputId}
            className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
          >
            {label}
          </label>
        )}
        <div className="relative">
          {leftIcon && (
            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <span className="text-gray-400 sm:text-sm">{leftIcon}</span>
            </div>
          )}
          <input
            id={inputId}
            ref={ref}
            className={`
              block w-full rounded-md border-gray-300 shadow-sm 
              focus:border-blue-500 focus:ring-blue-500 
              dark:bg-gray-700 dark:border-gray-600 dark:text-white
              dark:focus:border-blue-400 dark:focus:ring-blue-400
              ${leftIcon ? 'pl-10' : 'pl-3'}
              ${rightIcon || error ? 'pr-10' : 'pr-3'}
              ${error ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''}
              ${className || ''}
            `}
            {...props}
          />
          {(rightIcon || error) && (
            <div className="absolute inset-y-0 right-0 pr-3 flex items-center">
              {error ? (
                <ExclamationCircleIcon className="h-5 w-5 text-red-500" />
              ) : (
                rightIcon && <span className="text-gray-400 sm:text-sm">{rightIcon}</span>
              )}
            </div>
          )}
        </div>
        {error && (
          <p className="mt-1 text-sm text-red-600 dark:text-red-400">{error}</p>
        )}
        {helperText && !error && (
          <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">{helperText}</p>
        )}
      </div>
    );
  }
);

Input.displayName = 'Input';

export default Input;