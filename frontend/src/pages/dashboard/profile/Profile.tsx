import React from 'react';
import { useTranslation } from 'react-i18next';
import { PendingImplementation } from '../../../components/common';

const Profile: React.FC = () => {
  const { t } = useTranslation();
  
  return (
    <div className="profile-page">
      <h1 className="text-2xl font-bold mb-6">
        {t('profile.title', 'My Profile')}
      </h1>
      
      <PendingImplementation 
        title={t('profile.pendingTitle', 'Profile Page Coming Soon')}
        message={t('profile.pendingMessage', 'The profile page is currently under development. You will be able to view and edit your profile information here.')}
        backUrl="/dashboard"
        backText={t('profile.backToDashboard', 'Back to Dashboard')}
      />
    </div>
  );
};

export default Profile;