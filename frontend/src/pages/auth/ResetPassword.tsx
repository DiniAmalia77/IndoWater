import React, { useState, useEffect } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import api from '../../services/api';

const ResetPassword: React.FC = () => {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const location = useLocation();
  
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [token, setToken] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    // Extract token and email from URL query parameters
    const queryParams = new URLSearchParams(location.search);
    const tokenParam = queryParams.get('token');
    const emailParam = queryParams.get('email');
    
    if (tokenParam) {
      setToken(tokenParam);
    }
    
    if (emailParam) {
      setEmail(emailParam);
    }
  }, [location]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    
    // Validate password match
    if (password !== passwordConfirmation) {
      setError(t('auth.resetPassword.passwordMismatch'));
      return;
    }
    
    setLoading(true);

    try {
      await api.post('/auth/reset-password', {
        email,
        password,
        password_confirmation: passwordConfirmation,
        token
      });
      
      // Redirect to login page with success message
      navigate('/login', { 
        state: { 
          message: t('auth.resetPassword.success'),
          type: 'success'
        } 
      });
    } catch (err: any) {
      if (err.response && err.response.data && err.response.data.message) {
        setError(err.response.data.message);
      } else if (err.response && err.response.data && err.response.data.errors) {
        // Handle validation errors
        const validationErrors = Object.values(err.response.data.errors).flat();
        setError(validationErrors.join(', '));
      } else {
        setError(t('auth.resetPassword.error'));
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="reset-password-page">
      <div className="reset-password-container">
        <h2>{t('auth.resetPassword.title')}</h2>
        
        {error && <div className="alert alert-danger">{error}</div>}
        
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label htmlFor="email">{t('auth.resetPassword.email')}</label>
            <input
              type="email"
              id="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              readOnly={!!email}
            />
          </div>
          
          <div className="form-group">
            <label htmlFor="password">{t('auth.resetPassword.password')}</label>
            <input
              type="password"
              id="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              minLength={8}
              autoFocus
            />
          </div>
          
          <div className="form-group">
            <label htmlFor="passwordConfirmation">{t('auth.resetPassword.passwordConfirmation')}</label>
            <input
              type="password"
              id="passwordConfirmation"
              value={passwordConfirmation}
              onChange={(e) => setPasswordConfirmation(e.target.value)}
              required
              minLength={8}
            />
          </div>
          
          <div className="form-group">
            <button 
              type="submit" 
              className="btn btn-primary btn-block"
              disabled={loading}
            >
              {loading ? t('auth.resetPassword.resetting') : t('auth.resetPassword.submit')}
            </button>
          </div>
        </form>
        
        <div className="auth-links">
          <Link to="/login">{t('auth.resetPassword.backToLogin')}</Link>
        </div>
      </div>
    </div>
  );
};

export default ResetPassword;