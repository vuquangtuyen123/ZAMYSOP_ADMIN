<?php
require_once __DIR__ . '/../model/dashboard_model.php';

class DashboardController {

    /** Trang Dashboard chính */
    public function index() {
        $filterType = $_GET['type'] ?? null;
        $filterValue = $_GET['value'] ?? null;

        $summary = DashboardModel::layTongQuan($filterType, $filterValue);
        $categoryRevenue = DashboardModel::layDoanhThuTheoDanhMuc($filterType, $filterValue);
        $topProducts = DashboardModel::layTop5SanPham($filterType, $filterValue);
        $cancelStats = DashboardModel::layTop5TyLeHuy($filterType, $filterValue);
        $returnStats = DashboardModel::layTop5TyLeHoan($filterType, $filterValue);

        include __DIR__ . '/../view/dashboard.php';
    }

    /** API cho biểu đồ doanh thu (trả JSON sạch) */
    public function apiRevenue() {
        header('Content-Type: application/json; charset=utf-8');
        $type = $_GET['type'] ?? 'month';
        $value = $_GET['value'] ?? null;

        $data = DashboardModel::layDoanhThuTheoThoiGian($type, $value);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>
