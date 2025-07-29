import React from 'react';
import { useTranslation } from 'react-i18next';
import {
  UsersIcon,
  BuildingOfficeIcon,
  HomeModernIcon,
  CpuChipIcon,
  CurrencyDollarIcon,
  ExclamationTriangleIcon,
} from '@heroicons/react/24/outline';
import Card from '../../components/common/Card';
import { useAuthStore } from '../../store/authStore';
import { formatCurrency, formatNumber } from '../../utils';
import { Line, Doughnut } from 'react-chartjs-2';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
} from 'chart.js';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  ArcElement
);

const DashboardPage: React.FC = () => {
  const { user } = useAuthStore();
  const { t } = useTranslation();

  // Mock data - in real app, this would come from API
  const stats = {
    totalCustomers: 1250,
    totalProperties: 890,
    totalMeters: 1100,
    totalRevenue: 125000000,
    activeMeters: 1050,
    pendingPayments: 25,
    meterStatusDistribution: {
      active: 1050,
      inactive: 30,
      maintenance: 15,
      error: 5,
    },
  };

  const revenueData = {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    datasets: [
      {
        label: 'Revenue',
        data: [15000000, 18000000, 22000000, 19000000, 25000000, 28000000],
        borderColor: 'rgb(59, 130, 246)',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.4,
      },
    ],
  };

  const meterStatusData = {
    labels: ['Active', 'Inactive', 'Maintenance', 'Error'],
    datasets: [
      {
        data: [
          stats.meterStatusDistribution.active,
          stats.meterStatusDistribution.inactive,
          stats.meterStatusDistribution.maintenance,
          stats.meterStatusDistribution.error,
        ],
        backgroundColor: [
          'rgba(34, 197, 94, 0.8)',
          'rgba(156, 163, 175, 0.8)',
          'rgba(251, 191, 36, 0.8)',
          'rgba(239, 68, 68, 0.8)',
        ],
        borderColor: [
          'rgba(34, 197, 94, 1)',
          'rgba(156, 163, 175, 1)',
          'rgba(251, 191, 36, 1)',
          'rgba(239, 68, 68, 1)',
        ],
        borderWidth: 1,
      },
    ],
  };

  const StatCard: React.FC<{
    title: string;
    value: string | number;
    icon: React.ComponentType<{ className?: string }>;
    color: string;
    change?: string;
  }> = ({ title, value, icon: Icon, color, change }) => (
    <Card>
      <div className="flex items-center">
        <div className={`p-3 rounded-lg ${color}`}>
          <Icon className="h-6 w-6 text-white" />
        </div>
        <div className="ml-4">
          <p className="text-sm font-medium text-gray-600 dark:text-gray-400">{title}</p>
          <p className="text-2xl font-semibold text-gray-900 dark:text-white">{value}</p>
          {change && (
            <p className="text-sm text-green-600 dark:text-green-400">{change}</p>
          )}
        </div>
      </div>
    </Card>
  );

  const getStatsForRole = () => {
    switch (user?.role) {
      case 'superadmin':
        return [
          {
            title: t('dashboard.stats.totalCustomers'),
            value: formatNumber(stats.totalCustomers),
            icon: UsersIcon,
            color: 'bg-blue-500',
            change: '+12% from last month',
          },
          {
            title: t('dashboard.stats.totalClients'),
            value: formatNumber(45),
            icon: BuildingOfficeIcon,
            color: 'bg-green-500',
            change: '+5% from last month',
          },
          {
            title: t('dashboard.stats.totalProperties'),
            value: formatNumber(stats.totalProperties),
            icon: HomeModernIcon,
            color: 'bg-purple-500',
            change: '+8% from last month',
          },
          {
            title: t('dashboard.stats.totalRevenue'),
            value: formatCurrency(stats.totalRevenue),
            icon: CurrencyDollarIcon,
            color: 'bg-yellow-500',
            change: '+15% from last month',
          },
        ];
      case 'client':
        return [
          {
            title: t('dashboard.stats.myCustomers'),
            value: formatNumber(125),
            icon: UsersIcon,
            color: 'bg-blue-500',
            change: '+8% from last month',
          },
          {
            title: t('dashboard.stats.myProperties'),
            value: formatNumber(89),
            icon: HomeModernIcon,
            color: 'bg-green-500',
            change: '+5% from last month',
          },
          {
            title: t('dashboard.stats.activeMeters'),
            value: formatNumber(110),
            icon: CpuChipIcon,
            color: 'bg-purple-500',
            change: '+3% from last month',
          },
          {
            title: t('dashboard.stats.monthlyRevenue'),
            value: formatCurrency(12500000),
            icon: CurrencyDollarIcon,
            color: 'bg-yellow-500',
            change: '+12% from last month',
          },
        ];
      case 'customer':
        return [
          {
            title: t('dashboard.stats.myProperties'),
            value: formatNumber(2),
            icon: HomeModernIcon,
            color: 'bg-blue-500',
          },
          {
            title: t('dashboard.stats.activeMeters'),
            value: formatNumber(3),
            icon: CpuChipIcon,
            color: 'bg-green-500',
          },
          {
            title: t('dashboard.stats.creditBalance'),
            value: formatCurrency(250000),
            icon: CurrencyDollarIcon,
            color: 'bg-purple-500',
          },
          {
            title: t('dashboard.stats.pendingPayments'),
            value: formatNumber(1),
            icon: ExclamationTriangleIcon,
            color: 'bg-red-500',
          },
        ];
      default:
        return [];
    }
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-2xl font-semibold text-gray-900 dark:text-white">
          {t('dashboard.title')}
        </h1>
        <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
          {t('dashboard.welcome', { name: user?.firstName })}
        </p>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {getStatsForRole().map((stat, index) => (
          <StatCard key={index} {...stat} />
        ))}
      </div>

      {/* Charts */}
      {(user?.role === 'superadmin' || user?.role === 'client') && (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {/* Revenue Chart */}
          <Card>
            <h3 className="text-lg font-medium text-gray-900 dark:text-white mb-4">
              {t('dashboard.charts.revenue')}
            </h3>
            <div className="h-64">
              <Line
                data={revenueData}
                options={{
                  responsive: true,
                  maintainAspectRatio: false,
                  plugins: {
                    legend: {
                      display: false,
                    },
                  },
                  scales: {
                    y: {
                      beginAtZero: true,
                      ticks: {
                        callback: function(value) {
                          return formatCurrency(value as number);
                        },
                      },
                    },
                  },
                }}
              />
            </div>
          </Card>

          {/* Meter Status Chart */}
          <Card>
            <h3 className="text-lg font-medium text-gray-900 dark:text-white mb-4">
              {t('dashboard.charts.meterStatus')}
            </h3>
            <div className="h-64">
              <Doughnut
                data={meterStatusData}
                options={{
                  responsive: true,
                  maintainAspectRatio: false,
                  plugins: {
                    legend: {
                      position: 'bottom',
                    },
                  },
                }}
              />
            </div>
          </Card>
        </div>
      )}

      {/* Recent Activity */}
      <Card>
        <h3 className="text-lg font-medium text-gray-900 dark:text-white mb-4">
          {t('dashboard.recentActivity')}
        </h3>
        <div className="space-y-3">
          {[
            {
              action: 'New customer registered',
              user: 'John Doe',
              time: '2 minutes ago',
            },
            {
              action: 'Payment received',
              user: 'Jane Smith',
              time: '5 minutes ago',
            },
            {
              action: 'Meter reading updated',
              user: 'System',
              time: '10 minutes ago',
            },
            {
              action: 'New property added',
              user: 'Bob Johnson',
              time: '15 minutes ago',
            },
          ].map((activity, index) => (
            <div key={index} className="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
              <div>
                <p className="text-sm font-medium text-gray-900 dark:text-white">
                  {activity.action}
                </p>
                <p className="text-xs text-gray-500 dark:text-gray-400">
                  by {activity.user}
                </p>
              </div>
              <span className="text-xs text-gray-500 dark:text-gray-400">
                {activity.time}
              </span>
            </div>
          ))}
        </div>
      </Card>
    </div>
  );
};

export default DashboardPage;