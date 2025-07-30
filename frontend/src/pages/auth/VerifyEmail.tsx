import React, { useState, useEffect } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import api from '../../services/api';

const VerifyEmail: React.FC = () => {
  const { t } = useTranslation();
  const location = useLocation();
  const navigate = useNavigate();
  
  const [verifying, setVerifying] = useState(true);
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState('');
  const [email, setEmail] = useState('');
  const [resendLoading, setResendLoading] = useState(false);
  const [resendMessage, setResendMessage] = useState('');

  useEffect(() => {
    // Extract token and email from URL query parameters
    const queryParams = new URLSearchParams(location.search);
    const token = queryParams.get('token');
    const emailParam = queryParams.get('email');
    
    if (emailParam) {
      setEmail(emailParam);
    }
    
    if (token && emailParam) {
      verifyEmail(token, emailParam);
    } else {
      setVerifying(false);
      setError(t('auth.verifyEmail.invalidLink'));
    }
  }, [location, t]);

  const verifyEmail = async (token: string, email: string) => {
    try {
      await api.post('/auth/verify-email', { token, email });
      setSuccess(true);
      
      // Redirect to login after 3 seconds
      setTimeout(() => {
        navigate('/login', { 
          state: { 
            message: t('auth.verifyEmail.verifiedRedirect'),
            type: 'success'
          } 
        });
      }, 3000);
    } catch (err: any) {
      if (err.response && err.response.data && err.response.data.message) {
        setError(err.response.data.message);
      } else {
        setError(t('auth.verifyEmail.error'));
      }
    } finally {
      setVerifying(false);
    }
  };

  const handleResendVerification = async () => {
    setResendLoading(true);
    setResendMessage('');
    
    try {
      await api.post('/auth/resend-verification', { email });
      setResendMessage(t('auth.verifyEmail.resendSuccess'));
    } catch (err: any) {
      if (err.response && err.response.data && err.response.data.message) {
        setResendMessage(err.response.data.message);
      } else {
        setResendMessage(t('auth.verifyEmail.resendError'));
      }
    } finally {
      setResendLoading(false);
    }
  };

  return (
    <div className="verify-email-page">
      <div className="verify-email-container">
        <h2>{t('auth.verifyEmail.title')}</h2>
        
        {verifying ? (
          <div className="verifying">
            <p>{t('auth.verifyEmail.verifying')}</p>
            <div className="loading-spinner"></div>
          </div>
        ) : success ? (
          <div className="alert alert-success">
            <p>{t('auth.verifyEmail.success')}</p>
            <p>{t('auth.verifyEmail.redirecting')}</p>
          </div>
        ) : (
          <>
            <div className="alert alert-danger">{error}</div>
            
            {email && (
              <div className="resend-verification">
                <p>{t('auth.verifyEmail.resendPrompt')}</p>
                
                {resendMessage && (
                  <div className={`alert ${resendMessage === t('auth.verifyEmail.resendSuccess') ? 'alert-success' : 'alert-danger'}`}>
                    {resendMessage}
                  </div>
                )}
                
                <button 
                  onClick={handleResendVerification} 
                  className="btn btn-secondary"
                  disabled={resendLoading}
                >
                  {resendLoading ? t('auth.verifyEmail.resending') : t('auth.verifyEmail.resend')}
                </button>
              </div>
            )}
          </>
        )}
        
        <div className="auth-links">
          <Link to="/login">{t('auth.verifyEmail.backToLogin')}</Link>
        </div>
      </div>
    </div>
  );
};

export default VerifyEmail;