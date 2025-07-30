import React from 'react';
import { useTranslation } from 'react-i18next';
import { PendingImplementation } from '../../../components/common';

const PaymentManagement: React.FC = () => {
  const { t } = useTranslation();
  
  return (
    <div className="payment-management-page">
      <h1 className="text-2xl font-bold mb-6">
        {t('dashboard.client.paymentManagement.title', 'Payment Management')}
      </h1>
      
      <PendingImplementation 
        title={t('dashboard.client.paymentManagement.pendingTitle', 'Payment Management Coming Soon')}
        message={t('dashboard.client.paymentManagement.pendingMessage', 'The payment management interface is currently under development. You will be able to view, track, and manage payments here.')}
        backUrl="/dashboard/client"
        backText={t('dashboard.client.paymentManagement.backToDashboard', 'Back to Dashboard')}
      />
    </div>
  );
};

export default PaymentManagement;