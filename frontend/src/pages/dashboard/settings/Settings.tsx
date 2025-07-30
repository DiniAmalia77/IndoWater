import React from 'react';
import { useTranslation } from 'react-i18next';
import { PendingImplementation } from '../../../components/common';

const Settings: React.FC = () => {
  const { t } = useTranslation();
  
  return (
    <div className="settings-page">
      <h1 className="text-2xl font-bold mb-6">
        {t('settings.title', 'Settings')}
      </h1>
      
      <PendingImplementation 
        title={t('settings.pendingTitle', 'Settings Page Coming Soon')}
        message={t('settings.pendingMessage', 'The settings page is currently under development. You will be able to configure your account, notifications, and preferences here.')}
        backUrl="/dashboard"
        backText={t('settings.backToDashboard', 'Back to Dashboard')}
      />
    </div>
  );
};

export default Settings;