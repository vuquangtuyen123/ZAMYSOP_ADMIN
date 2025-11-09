<?php
require_once __DIR__ . '/../model/inventory_model.php';
require_once __DIR__ . '/../config/auth.php';

class InventoryController {
	private $model;
	private $itemsPerPage = 8;

	public function __construct() {
		$this->model = new InventoryModel();
		if (session_status() === PHP_SESSION_NONE) session_start();
	}

	public function index() {
		require_login();
		require_capability('inventory.upload');
		
		$results = $_SESSION['inventory_results'] ?? null;
		unset($_SESSION['inventory_results']);
		
		$search = trim($_GET['search'] ?? '');
		$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
		$offset = ($page - 1) * $this->itemsPerPage;
		
		$total = $this->model->getTotalInventory($search);
		$totalPages = max(1, ceil($total / $this->itemsPerPage));
		$inventory_list = $this->model->getInventoryList($search, $this->itemsPerPage, $offset);
		
		// Pass biến cho view
		$viewVars = [
			'inventory_list' => $inventory_list,
			'totalPages' => $totalPages,
			'page' => $page,
			'search' => $search,
			'results' => $results
		];
		extract($viewVars);
		
		require_once __DIR__ . '/../view/inventory/index.php';
	}

	public function upload() {
		require_login();
		require_capability('inventory.upload');
		
		if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['file'])) {
			header('Location: index.php?c=inventory&a=index');
			exit;
		}
		
		$file = $_FILES['file'];
		if ($file['error'] !== UPLOAD_ERR_OK) {
			$_SESSION['inventory_results'] = ['error' => 'Tải tệp thất bại'];
			header('Location: index.php?c=inventory&a=index');
			exit;
		}
		
		$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
		$tmp = $file['tmp_name'];
		$rows = [];
		
		if ($ext === 'csv') {
			$rows = $this->parseCsv($tmp);
		} elseif ($ext === 'xlsx' || $ext === 'xls') {
			$rows = $this->parseXlsxBasic($tmp);
		} else {
			$_SESSION['inventory_results'] = ['error' => 'Định dạng không hỗ trợ. Vui lòng dùng .xlsx, .xls hoặc .csv'];
			header('Location: index.php?c=inventory&a=index');
			exit;
		}
		
		if (isset($rows['error'])) {
			$_SESSION['inventory_results'] = $rows;
			header('Location: index.php?c=inventory&a=index');
			exit;
		}
		
		if (empty($rows) || count($rows) < 1) {
			$_SESSION['inventory_results'] = ['error' => 'File rỗng hoặc không có dữ liệu'];
			header('Location: index.php?c=inventory&a=index');
			exit;
		}
		
		// Normalize header để so sánh
		$header = array_map([$this, 'normalizeHeader'], $rows[0] ?? []);
		
		// Tìm index của các cột
		$idxId = $this->findColumnIndex($header, ['mabienthe', 'variantid', 'id']);
		$idxQty = $this->findColumnIndex($header, ['soluong', 'quantity', 'tonkho', 'stock']);
		
		if ($idxId === false || $idxQty === false) {
			$_SESSION['inventory_results'] = ['error' => 'Thiếu cột "Mã biến thể" hoặc "Số lượng". Vui lòng sử dụng file mẫu.'];
			header('Location: index.php?c=inventory&a=index');
			exit;
		}
		
		$success = 0; 
		$failed = 0; 
		$details = [];
		
		// Bắt đầu từ hàng thứ 2 (index 1)
		for ($i = 1; $i < count($rows); $i++) {
			$row = $rows[$i];
			$ma = isset($row[$idxId]) ? trim((string)$row[$idxId]) : '';
			$qty = isset($row[$idxQty]) ? trim((string)$row[$idxQty]) : '';
			
			// Skip hàng rỗng
			if ($ma === '' && $qty === '') {
				continue;
			}
			
			if ($ma === '' || $qty === '' || !is_numeric($qty)) {
				$failed++; 
				$details[] = [
					'row' => $i+1, 
					'ma_bien_the' => $ma, 
					'ton_kho' => $qty, 
					'result' => 'Dữ liệu không hợp lệ', 
					'debug' => null
				];
				continue;
			}
			
			$res = $this->model->updateVariantStock($ma, (int)$qty);
			
			if ($res['error']) { 
				$failed++; 
				$details[] = [
					'row' => $i+1, 
					'ma_bien_the' => $ma, 
					'ton_kho' => $qty, 
					'result' => 'Lỗi: ' . ($res['message'] ?? 'Không xác định'),
					'debug' => $res['debug'] ?? null
				]; 
			} else { 
				$success++; 
				$details[] = [
					'row' => $i+1, 
					'ma_bien_the' => $ma, 
					'ton_kho' => $qty, 
					'result' => 'OK',
					'debug' => $res['debug'] ?? null
				]; 
			}
		}
		
		$_SESSION['inventory_results'] = [
			'summary' => ['success' => $success, 'failed' => $failed, 'total' => ($success+$failed)],
			'details' => $details
		];
		
		header('Location: index.php?c=inventory&a=index');
		exit;
	}

	/**
	 * Chuẩn hóa tên cột để so sánh
	 */
	private function normalizeHeader($header) {
		// Remove UTF-8 BOM ở mọi vị trí
		$h = preg_replace('/\xEF\xBB\xBF/', '', (string)$header);
		
		// Remove các ký tự không nhìn thấy (control characters)
		$h = preg_replace('/[\x00-\x1F\x7F-\x9F]/u', '', $h);
		
		$h = trim($h);
		$h = mb_strtolower($h, 'UTF-8');
		
		// Loại bỏ khoảng trắng và ký tự đặc biệt
		$h = str_replace([' ', '_', '-', '.', '"', "'", '`'], '', $h);
		
		// Loại bỏ dấu tiếng Việt
		$h = $this->removeVietnameseTones($h);
		
		return $h;
	}

	/**
	 * Loại bỏ dấu tiếng Việt
	 */
	private function removeVietnameseTones($str) {
		$search = [
			'à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ',
			'è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ',
			'ì', 'í', 'ị', 'ỉ', 'ĩ',
			'ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ',
			'ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ',
			'ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ',
			'đ'
		];
		$replace = [
			'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
			'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
			'i', 'i', 'i', 'i', 'i',
			'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
			'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
			'y', 'y', 'y', 'y', 'y',
			'd'
		];
		return str_replace($search, $replace, $str);
	}

	/**
	 * Tìm index của cột dựa trên danh sách tên có thể
	 */
	private function findColumnIndex($header, $possibleNames) {
		foreach ($header as $idx => $col) {
			if (in_array($col, $possibleNames)) {
				return $idx;
			}
		}
		return false;
	}

	private function parseCsv($path) {
		$rows = [];
		if (($h = fopen($path, 'r')) === false) {
			return ['error' => 'Không thể đọc file CSV'];
		}
		
		while (($data = fgetcsv($h)) !== false) {
			// Skip completely empty lines
			if ($data === null) continue;
			
			// Loại bỏ BOM và trim cho tất cả cells
			$data = array_map(function($cell) {
				// Remove BOM UTF-8
				$cell = preg_replace('/\xEF\xBB\xBF/', '', $cell);
				// Remove control characters
				$cell = preg_replace('/[\x00-\x1F\x7F-\x9F]/u', '', $cell);
				return trim($cell);
			}, $data);
			
			$allEmpty = true;
			foreach ($data as $cell) { 
				if ($cell !== '') { 
					$allEmpty = false; 
					break; 
				} 
			}
			
			if ($allEmpty) continue;
			$rows[] = $data;
		}
		
		fclose($h);
		
		if (empty($rows)) {
			return ['error' => 'File CSV rỗng'];
		}
		
		return $rows;
	}

	/**
	 * Parse HTML table format (file .xls từ downloadSampleExcel)
	 */
	private function parseHtmlTable($html) {
		$rows = [];
		
		// Remove BOM
		$html = preg_replace('/\xEF\xBB\xBF/', '', $html);
		
		// Parse HTML
		$dom = new DOMDocument();
		@$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
		
		$tables = $dom->getElementsByTagName('table');
		if ($tables->length == 0) {
			return ['error' => 'Không tìm thấy bảng trong file'];
		}
		
		$table = $tables->item(0);
		$trs = $table->getElementsByTagName('tr');
		
		foreach ($trs as $tr) {
			$row = [];
			
			// Lấy cả th và td
			$cells = $tr->getElementsByTagName('td');
			if ($cells->length == 0) {
				$cells = $tr->getElementsByTagName('th');
			}
			
			foreach ($cells as $cell) {
				$val = trim($cell->textContent);
				$val = preg_replace('/\xEF\xBB\xBF/', '', $val);
				$val = preg_replace('/[\x00-\x1F\x7F-\x9F]/u', '', $val);
				$row[] = trim($val);
			}
			
			if (!empty($row)) {
				$rows[] = $row;
			}
		}
		
		if (empty($rows)) {
			return ['error' => 'File không có dữ liệu'];
		}
		
		return $rows;
	}

	private function parseXlsxBasic($path) {
		// Đọc nội dung file để kiểm tra loại
		$content = file_get_contents($path);
		
		// Kiểm tra xem có phải file HTML table không (từ downloadSampleExcel)
		if (stripos($content, '<table') !== false && stripos($content, '</table>') !== false) {
			return $this->parseHtmlTable($content);
		}
		
		// Nếu không có ZipArchive, thử parse như CSV
		if (!class_exists('ZipArchive')) {
			return $this->parseCsv($path);
		}
		
		$zip = new ZipArchive();
		$openResult = $zip->open($path);
		
		if ($openResult !== true) {
			// Nếu không mở được như XLSX, thử parse như CSV
			return $this->parseCsv($path);
		}
		
		$sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
		$sharedXml = $zip->getFromName('xl/sharedStrings.xml');
		$zip->close();
		
		if ($sheetXml === false) {
			// Nếu không có sheet1, thử parse như CSV
			return $this->parseCsv($path);
		}
		
		// Parse shared strings
		$shared = [];
		if ($sharedXml) {
			$ss = @simplexml_load_string($sharedXml);
			if ($ss && isset($ss->si)) {
				foreach ($ss->si as $si) { 
					$str = (string)($si->t ?? '');
					// Remove BOM và control characters từ shared strings
					$str = preg_replace('/\xEF\xBB\xBF/', '', $str);
					$str = preg_replace('/[\x00-\x1F\x7F-\x9F]/u', '', $str);
					$shared[] = trim($str);
				}
			}
		}
		
		// Parse sheet data
		$rows = [];
		$xml = @simplexml_load_string($sheetXml);
		if (!$xml || !isset($xml->sheetData->row)) {
			return $this->parseCsv($path);
		}
		
		foreach ($xml->sheetData->row as $r) {
			$row = [];
			if (!isset($r->c)) continue;
			
			foreach ($r->c as $c) {
				$type = (string)($c['t'] ?? '');
				$val = isset($c->v) ? (string)$c->v : '';
				
				if ($type === 's') {
					// Shared string
					$idx = (int)$val; 
					$val = $shared[$idx] ?? '';
				}
				
				// Clean value
				$val = preg_replace('/\xEF\xBB\xBF/', '', $val);
				$val = preg_replace('/[\x00-\x1F\x7F-\x9F]/u', '', $val);
				$val = trim($val);
				
				$row[] = $val;
			}
			
			// Chỉ thêm row nếu không rỗng
			if (!empty($row)) {
				$rows[] = $row;
			}
		}
		
		if (empty($rows)) {
			return $this->parseCsv($path);
		}
		
		return $rows;
	}

	/**
	 * Xuất danh sách tồn kho ra CSV
	 */
	public function exportCsv() {
		require_login();
		require_capability('inventory.upload');
		
		$result = $this->model->getAllVariantsForExport();
		if ($result['error']) {
			$_SESSION['inventory_results'] = ['error' => 'Không thể lấy dữ liệu tồn kho'];
			header('Location: index.php?c=inventory&a=index');
			exit;
		}
		
		header('Content-Type: text/csv; charset=UTF-8');
		header('Content-Disposition: attachment; filename="ton_kho_' . date('Y-m-d_His') . '.csv"');
		
		$output = fopen('php://output', 'w');
		
		// BOM cho UTF-8
		fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
		
		// Header
		fputcsv($output, ['Mã biến thể', 'Tồn kho']);
		
		// Data
		foreach ($result['data'] as $row) {
			fputcsv($output, [
				$row['ma_bien_the'],
				$row['ton_kho']
			]);
		}
		
		fclose($output);
		exit;
	}

	/**
	 * Tải file CSV mẫu nhập tồn kho
	 */
	public function downloadSampleCsv() {
		require_login();
		require_capability('inventory.upload');
		
		header('Content-Type: text/csv; charset=UTF-8');
		header('Content-Disposition: attachment; filename="Mau_nhap_ton_kho.csv"');
		
		$output = fopen('php://output', 'w');
		
		// BOM UTF-8 cho Excel
		fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
		
		// Header
		fputcsv($output, ['Mã biến thể', 'Số lượng']);
		
		// Ví dụ dữ liệu
		fputcsv($output, ['1', '3']);
		fputcsv($output, ['2', '5']);
		
		fclose($output);
		exit;
	}

	/**
	 * Tải file Excel mẫu nhập tồn kho (.xls)
	 * Sử dụng HTML table format - đơn giản và Excel mở được
	 */
	public function downloadSampleExcel() {
		require_login();
		require_capability('inventory.upload');
		
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment; filename="Mau_nhap_ton_kho.xls"');
		
		// Sử dụng HTML table - Excel sẽ tự động tách thành các cột
		echo '
		<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		</head>
		<body>
			<table border="1">
				<tr>
					<th>Mã biến thể</th>
					<th>Số lượng</th>
				</tr>
				<tr>
					<td>1</td>
					<td>3</td>
				</tr>
				<tr>
					<td>2</td>
					<td>5</td>
				</tr>
			</table>
		</body>
		</html>';
		
		exit;
	}
}
?>