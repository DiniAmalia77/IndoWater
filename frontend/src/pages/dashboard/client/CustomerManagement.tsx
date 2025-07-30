import React from 'react';
import { useTranslation } from 'react-i18next';
import { PendingImplementation } from '../../../components/common';

const CustomerManagement: React.FC = () => {
  const { t } = useTranslation();
  
  return (
    <div className="customer-management-page">
      <h1 className="text-2xl font-bold mb-6">
        {t('dashboard.client.customerManagement.title', 'Customer Management')}
      </h1>
      
      <PendingImplementation 
        title={t('dashboard.client.customerManagement.pendingTitle', 'Customer Management Coming Soon')}
        message={t('dashboard.client.customerManagement.pendingMessage', 'The customer management interface is currently under development. You will be able to add, edit, and manage customers here.')}
        backUrl="/dashboard/client"
        backText={t('dashboard.client.customerManagement.backToDashboard', 'Back to Dashboard')}
      />
    </div>
  );
};

export default CustomerManagement;