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
        
        // Calculate totals
        $totalRevenue = 0;
        $totalOrders = 0;
        foreach ($revenueByMonth as $rev) {
            $totalRevenue += $rev['revenue'];
            $totalOrders += $rev['order_count'];
        }

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

        $data = [
            'page_title' => 'Báo cáo Doanh thu & KPI',
            'current_month' => $month,
            'current_year' => $year,
            'revenue_by_month' => $revenueByMonth,
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
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
        
        $data = [
            'page_title' => 'Thống kê nhanh',
            'stats' => $stats,
            'current_role' => $this->getRole()
        ];

        $this->view('admin/reports/stats', $data, 'admin');
    }
}
