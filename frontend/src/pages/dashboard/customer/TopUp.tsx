import React from 'react';
import { useTranslation } from 'react-i18next';
import { PendingImplementation } from '../../../components/common';

const TopUp: React.FC = () => {
  const { t } = useTranslation();
  
  return (
    <div className="top-up-page">
      <h1 className="text-2xl font-bold mb-6">
        {t('dashboard.customer.topUp.title', 'Top Up Credit')}
      </h1>
      
      <PendingImplementation 
        title={t('dashboard.customer.topUp.pendingTitle', 'Top Up Feature Coming Soon')}
        message={t('dashboard.customer.topUp.pendingMessage', 'The top up feature is currently under development. You will be able to add credit to your account using various payment methods here.')}
        backUrl="/dashboard/customer"
        backText={t('dashboard.customer.topUp.backToDashboard', 'Back to Dashboard')}
      />
    </div>
  );
};

export default TopUp;