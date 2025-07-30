import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import api from '../../services/api';

const ForgotPassword: React.FC = () => {
  const { t } = useTranslation();
  
  const [email, setEmail] = useState('');
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setMessage('');
    setLoading(true);

    try {
      await api.post('/auth/forgot-password', { email });
      setMessage(t('auth.forgotPassword.success'));
      setEmail(''); // Clear the form
    } catch (err: any) {
      if (err.response && err.response.data && err.response.data.message) {
        setError(err.response.data.message);
      } else {
        setError(t('auth.forgotPassword.error'));
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="forgot-password-page">
      <div className="forgot-password-container">
        <h2>{t('auth.forgotPassword.title')}</h2>
        <p className="description">{t('auth.forgotPassword.description')}</p>
        
        {message && <div className="alert alert-success">{message}</div>}
        {error && <div className="alert alert-danger">{error}</div>}
        
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label htmlFor="email">{t('auth.forgotPassword.email')}</label>
            <input
              type="email"
              id="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              autoFocus
            />
          </div>
          
          <div className="form-group">
            <button 
              type="submit" 
              className="btn btn-primary btn-block"
              disabled={loading}
            >
              {loading ? t('auth.forgotPassword.sending') : t('auth.forgotPassword.submit')}
            </button>
          </div>
        </form>
        
        <div className="auth-links">
          <Link to="/login">{t('auth.forgotPassword.backToLogin')}</Link>
        </div>
      </div>
    </div>
  );
};

export default ForgotPassword;