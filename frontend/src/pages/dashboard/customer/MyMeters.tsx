import React from 'react';
import { useTranslation } from 'react-i18next';
import { PendingImplementation } from '../../../components/common';

const MyMeters: React.FC = () => {
  const { t } = useTranslation();
  
  return (
    <div className="my-meters-page">
      <h1 className="text-2xl font-bold mb-6">
        {t('dashboard.customer.myMeters.title', 'My Meters')}
      </h1>
      
      <PendingImplementation 
        title={t('dashboard.customer.myMeters.pendingTitle', 'Meter Management Coming Soon')}
        message={t('dashboard.customer.myMeters.pendingMessage', 'The meter management interface is currently under development. You will be able to view your meters and consumption data here.')}
        backUrl="/dashboard/customer"
        backText={t('dashboard.customer.myMeters.backToDashboard', 'Back to Dashboard')}
      />
    </div>
  );
};

export default MyMeters;