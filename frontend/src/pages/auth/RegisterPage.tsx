import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useFormik } from 'formik';
import * as Yup from 'yup';
import { toast } from 'react-toastify';
import { EyeIcon, EyeSlashIcon } from '@heroicons/react/24/outline';
import { useAuthStore } from '../../store/authStore';
import Button from '../../components/common/Button';
import Input from '../../components/common/Input';
import { useTranslation } from 'react-i18next';

const RegisterPage: React.FC = () => {
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const { register, isLoading } = useAuthStore();
  const navigate = useNavigate();
  const { t } = useTranslation();

  const validationSchema = Yup.object({
    firstName: Yup.string()
      .min(2, t('validation.firstName.minLength'))
      .required(t('validation.firstName.required')),
    lastName: Yup.string()
      .min(2, t('validation.lastName.minLength'))
      .required(t('validation.lastName.required')),
    email: Yup.string()
      .email(t('validation.email.invalid'))
      .required(t('validation.email.required')),
    password: Yup.string()
      .min(6, t('validation.password.minLength'))
      .required(t('validation.password.required')),
    confirmPassword: Yup.string()
      .oneOf([Yup.ref('password')], t('validation.confirmPassword.match'))
      .required(t('validation.confirmPassword.required')),
    role: Yup.string()
      .oneOf(['client', 'customer'], t('validation.role.invalid'))
      .required(t('validation.role.required')),
  });

  const formik = useFormik({
    initialValues: {
      firstName: '',
      lastName: '',
      email: '',
      password: '',
      confirmPassword: '',
      role: 'customer',
    },
    validationSchema,
    onSubmit: async (values) => {
      try {
        const { confirmPassword, ...registerData } = values;
        await register(registerData);
        toast.success(t('auth.register.success'));
        navigate('/dashboard');
      } catch (error: any) {
        toast.error(error.response?.data?.message || t('auth.register.error'));
      }
    },
  });

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full space-y-8">
        <div>
          <div className="mx-auto h-12 w-12 bg-blue-600 rounded-lg flex items-center justify-center">
            <span className="text-white font-bold text-xl">IW</span>
          </div>
          <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
            {t('auth.register.title')}
          </h2>
          <p className="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
            {t('auth.register.subtitle')}{' '}
            <Link
              to="/login"
              className="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400"
            >
              {t('auth.login.link')}
            </Link>
          </p>
        </div>
        <form className="mt-8 space-y-6" onSubmit={formik.handleSubmit}>
          <div className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
              <Input
                id="firstName"
                name="firstName"
                type="text"
                autoComplete="given-name"
                label={t('common.firstName')}
                placeholder={t('auth.register.firstNamePlaceholder')}
                value={formik.values.firstName}
                onChange={formik.handleChange}
                onBlur={formik.handleBlur}
                error={formik.touched.firstName && formik.errors.firstName ? formik.errors.firstName : undefined}
              />

              <Input
                id="lastName"
                name="lastName"
                type="text"
                autoComplete="family-name"
                label={t('common.lastName')}
                placeholder={t('auth.register.lastNamePlaceholder')}
                value={formik.values.lastName}
                onChange={formik.handleChange}
                onBlur={formik.handleBlur}
                error={formik.touched.lastName && formik.errors.lastName ? formik.errors.lastName : undefined}
              />
            </div>

            <Input
              id="email"
              name="email"
              type="email"
              autoComplete="email"
              label={t('common.email')}
              placeholder={t('auth.register.emailPlaceholder')}
              value={formik.values.email}
              onChange={formik.handleChange}
              onBlur={formik.handleBlur}
              error={formik.touched.email && formik.errors.email ? formik.errors.email : undefined}
            />

            <div>
              <label htmlFor="role" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                {t('common.role')}
              </label>
              <select
                id="role"
                name="role"
                value={formik.values.role}
                onChange={formik.handleChange}
                onBlur={formik.handleBlur}
                className="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-400"
              >
                <option value="customer">{t('roles.customer')}</option>
                <option value="client">{t('roles.client')}</option>
              </select>
              {formik.touched.role && formik.errors.role && (
                <p className="mt-1 text-sm text-red-600 dark:text-red-400">{formik.errors.role}</p>
              )}
            </div>

            <Input
              id="password"
              name="password"
              type={showPassword ? 'text' : 'password'}
              autoComplete="new-password"
              label={t('common.password')}
              placeholder={t('auth.register.passwordPlaceholder')}
              value={formik.values.password}
              onChange={formik.handleChange}
              onBlur={formik.handleBlur}
              error={formik.touched.password && formik.errors.password ? formik.errors.password : undefined}
              rightIcon={
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="text-gray-400 hover:text-gray-500"
                >
                  {showPassword ? (
                    <EyeSlashIcon className="h-5 w-5" />
                  ) : (
                    <EyeIcon className="h-5 w-5" />
                  )}
                </button>
              }
            />

            <Input
              id="confirmPassword"
              name="confirmPassword"
              type={showConfirmPassword ? 'text' : 'password'}
              autoComplete="new-password"
              label={t('common.confirmPassword')}
              placeholder={t('auth.register.confirmPasswordPlaceholder')}
              value={formik.values.confirmPassword}
              onChange={formik.handleChange}
              onBlur={formik.handleBlur}
              error={formik.touched.confirmPassword && formik.errors.confirmPassword ? formik.errors.confirmPassword : undefined}
              rightIcon={
                <button
                  type="button"
                  onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                  className="text-gray-400 hover:text-gray-500"
                >
                  {showConfirmPassword ? (
                    <EyeSlashIcon className="h-5 w-5" />
                  ) : (
                    <EyeIcon className="h-5 w-5" />
                  )}
                </button>
              }
            />
          </div>

          <div>
            <Button
              type="submit"
              className="w-full"
              loading={isLoading}
              disabled={!formik.isValid || isLoading}
            >
              {t('auth.register.submit')}
            </Button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default RegisterPage;