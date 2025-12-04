<?php

namespace Admin;

use BaseController;
use Order;
use Permission;

require_once __DIR__ . '/../BaseController.php';
require_once __DIR__ . '/../../Models/Order.php';

class AdminReportController extends BaseController
{
    private $orderModel;

    public function __construct()
    {
        parent::__construct();
        
        // Require admin or employee role
        $this->requireAnyRole(['admin', 'employee']);
        $this->requirePermission(Permission::VIEW_REPORTS);
        
        $this->orderModel = new Order();
    }

    public function revenue()
    {
        $month = $this->get('month', date('m'));
        $year = $this->get('year', date('Y'));

        // Get revenue data
        $revenueByMonth = $this->orderModel->getRevenueByMonth($year);
        
        // Get aggregate metrics for the year
        $aggregateMetrics = $this->orderModel->getAggregateMetrics($year);
        
        // Get revenue by category
        $revenueByCategory = $this->orderModel->getRevenueByCategory($year);
        
        // Get top products
        $topProducts = $this->orderModel->getTopProducts(10, $year);
        
        // Get order status distribution
        $statusDistribution = $this->orderModel->getOrderStatusDistribution($year);

        // Get KPI data
        $kpiData = $this->orderModel->getEmployeeKPI($month, $year);
        
        // Get previous month data for comparison
        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }
        $prevKpiData = $this->orderModel->getTotalSalesByMonth($prevMonth, $prevYear);
        
        // Calculate growth rate
        $prevYearMetrics = $this->orderModel->getAggregateMetrics($year - 1);
        $revenueGrowth = 0;
        if ($prevYearMetrics['total_revenue'] > 0) {
            $revenueGrowth = (($aggregateMetrics['total_revenue'] - $prevYearMetrics['total_revenue']) / $prevYearMetrics['total_revenue']) * 100;
        }

        $data = [
            'page_title' => 'Báo cáo Doanh thu & KPI',
            'current_month' => $month,
            'current_year' => $year,
            'revenue_by_month' => $revenueByMonth,
            'aggregate_metrics' => $aggregateMetrics,
            'revenue_by_category' => $revenueByCategory,
            'top_products' => $topProducts,
            'status_distribution' => $statusDistribution,
            'revenue_growth' => $revenueGrowth,
            'kpi_data' => $kpiData,
            'prev_kpi_data' => $prevKpiData,
            'prev_month' => $prevMonth,
            'prev_year' => $prevYear,
            'current_role' => $this->getRole(),
            'current_user_name' => $_SESSION['name'] ?? ''
        ];

        $this->view('admin/reports/revenue', $data, 'admin');
    }

    public function stats()
    {
        $stats = $this->orderModel->getQuickStats();
        $customerStats = $this->orderModel->getCustomerStats();
        $dailyTrend = $this->orderModel->getDailyRevenueTrend(30);
        $topCustomers = $this->orderModel->getTopCustomers(5);
        $recentOrders = $this->orderModel->getRecentOrders(10);
        $lowStockProducts = $this->orderModel->getLowStockProducts(10);
        
        // Merge customer stats into main stats
        $stats = array_merge($stats, $customerStats);
        
        // Calculate today vs yesterday
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        $todayRevenue = 0;
        $yesterdayRevenue = 0;
        foreach ($dailyTrend as $day) {
            if ($day['date'] == $today) {
                $todayRevenue = $day['revenue'];
            } elseif ($day['date'] == $yesterday) {
                $yesterdayRevenue = $day['revenue'];
            }
        }
        
        $stats['today_revenue'] = $todayRevenue;
        $stats['yesterday_revenue'] = $yesterdayRevenue;
        
        $data = [
            'page_title' => 'Thống kê chi tiết',
            'stats' => $stats,
            'daily_trend' => $dailyTrend,
            'top_customers' => $topCustomers,
            'recent_orders' => $recentOrders,
            'low_stock_products' => $lowStockProducts,
            'current_role' => $this->getRole()
        ];

        $this->view('admin/reports/stats', $data, 'admin');
    }
}
