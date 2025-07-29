import React from 'react';
import { useTranslation } from 'react-i18next';
import { PendingImplementation } from '../../../components/common';

const ConsumptionHistory: React.FC = () => {
  const { t } = useTranslation();
  
  return (
    <div className="consumption-history-page">
      <h1 className="text-2xl font-bold mb-6">
        {t('dashboard.customer.consumptionHistory.title', 'Consumption History')}
      </h1>
      
      <PendingImplementation 
        title={t('dashboard.customer.consumptionHistory.pendingTitle', 'Consumption History Coming Soon')}
        message={t('dashboard.customer.consumptionHistory.pendingMessage', 'The consumption history interface is currently under development. You will be able to view your water usage patterns and analytics here.')}
        backUrl="/dashboard/customer"
        backText={t('dashboard.customer.consumptionHistory.backToDashboard', 'Back to Dashboard')}
      />
    </div>
  );
};

export default ConsumptionHistory;