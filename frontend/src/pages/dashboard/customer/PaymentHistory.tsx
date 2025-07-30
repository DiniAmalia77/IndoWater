import React from 'react';
import { useTranslation } from 'react-i18next';
import { PendingImplementation } from '../../../components/common';

const PaymentHistory: React.FC = () => {
  const { t } = useTranslation();
  
  return (
    <div className="payment-history-page">
      <h1 className="text-2xl font-bold mb-6">
        {t('dashboard.customer.paymentHistory.title', 'Payment History')}
      </h1>
      
      <PendingImplementation 
        title={t('dashboard.customer.paymentHistory.pendingTitle', 'Payment History Coming Soon')}
        message={t('dashboard.customer.paymentHistory.pendingMessage', 'The payment history interface is currently under development. You will be able to view your payment history and download receipts here.')}
        backUrl="/dashboard/customer"
        backText={t('dashboard.customer.paymentHistory.backToDashboard', 'Back to Dashboard')}
      />
    </div>
  );
};

export default PaymentHistory;