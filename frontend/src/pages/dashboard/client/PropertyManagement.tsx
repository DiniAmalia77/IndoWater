import React from 'react';
import { useTranslation } from 'react-i18next';
import { PendingImplementation } from '../../../components/common';

const PropertyManagement: React.FC = () => {
  const { t } = useTranslation();
  
  return (
    <div className="property-management-page">
      <h1 className="text-2xl font-bold mb-6">
        {t('dashboard.client.propertyManagement.title', 'Property Management')}
      </h1>
      
      <PendingImplementation 
        title={t('dashboard.client.propertyManagement.pendingTitle', 'Property Management Coming Soon')}
        message={t('dashboard.client.propertyManagement.pendingMessage', 'The property management interface is currently under development. You will be able to add, edit, and manage properties here.')}
        backUrl="/dashboard/client"
        backText={t('dashboard.client.propertyManagement.backToDashboard', 'Back to Dashboard')}
      />
    </div>
  );
};

export default PropertyManagement;