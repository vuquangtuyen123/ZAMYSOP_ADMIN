/**
 * Dashboard JavaScript - Xử lý tương tác giao diện Dashboard
 * 
 * File này chứa tất cả JavaScript logic cho trang dashboard admin:
 * - Dropdown menu trong sidebar
 * - Tìm kiếm
 * - Keyboard navigation
 * - Responsive menu handling
 * 
 * @author Đội phát triển
 * @version 1.0
 */

/**
 * Xử lý dropdown menu trong sidebar
 * 
 * Tính năng:
 * - Mở/đóng submenu khi click
 * - Đóng submenu khác khi mở submenu mới
 * - Xoay icon mũi tên khi mở/đóng
 * - Accessibility support với keyboard navigation
 */
function xuLyDropdownMenu() {
    const dropdownButtons = document.querySelectorAll('.nut-mo-dong');
    
    dropdownButtons.forEach(button => {
        // Xử lý click event
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const submenu = document.getElementById(targetId);
            const icon = this.querySelector('.icon-mo-dong');
            
            // Đóng tất cả submenu khác để chỉ có 1 submenu mở tại 1 thời điểm
            document.querySelectorAll('.menu-con').forEach(menu => {
                if (menu.id !== targetId) {
                    menu.classList.remove('hien-thi');
                }
            });
            
            // Đặt lại tất cả icon khác về trạng thái ban đầu
            document.querySelectorAll('.icon-mo-dong').forEach(i => {
                if (i !== icon) {
                    i.classList.remove('xoay');
                }
            });
            
            // Toggle submenu hiện tại (mở nếu đang đóng, đóng nếu đang mở)
            submenu.classList.toggle('hien-thi');
            icon.classList.toggle('xoay');
        });
        
        // Thêm hỗ trợ keyboard navigation cho accessibility
        button.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
}

/**
 * Xử lý tìm kiếm
 * 
 * Hiện tại chỉ là placeholder, sẽ được implement đầy đủ trong tương lai
 * với các tính năng:
 * - Auto-complete
 * - Search suggestions
 * - Filter results
 * - Search history
 */
function xuLyTimKiem() {
    const searchInput = document.querySelector('.hop-tim-kiem input');
    const searchTerm = searchInput.value.trim();
    
    if (searchTerm) {
        // TODO: Implement search functionality
        // Có thể gọi API để tìm kiếm sản phẩm, đơn hàng, người dùng...
        console.log('Tìm kiếm:', searchTerm);
        
        // Placeholder: Hiển thị thông báo
        alert(`Đang tìm kiếm: "${searchTerm}"\n(Tính năng này sẽ được phát triển trong tương lai)`);
    } else {
        alert('Vui lòng nhập từ khóa tìm kiếm');
    }
}

/**
 * Khởi tạo event listeners cho tìm kiếm
 */
function khoiTaoTimKiem() {
    // Xử lý tìm kiếm khi nhấn Enter
    const searchInput = document.querySelector('.hop-tim-kiem input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                xuLyTimKiem();
            }
        });
    }
    
    // Xử lý tìm kiếm khi click icon search
    const searchIcon = document.querySelector('.hop-tim-kiem i');
    if (searchIcon) {
        searchIcon.addEventListener('click', function() {
            xuLyTimKiem();
        });
    }
}

/**
 * Xử lý responsive menu
 * 
 * TODO: Implement hamburger menu cho mobile devices
 * - Toggle sidebar on mobile
 * - Overlay background
 * - Touch gestures
 */
function xuLyResponsiveMenu() {
    // Placeholder cho responsive menu
    // Sẽ được implement khi có CSS responsive
    console.log('Responsive menu handler initialized');
}

/**
 * Highlight menu item hiện tại
 * 
 * Đánh dấu menu item tương ứng với trang hiện tại
 */
function highlightMenuHienTai() {
    // Lấy URL hiện tại
    const currentUrl = window.location.search;
    const urlParams = new URLSearchParams(currentUrl);
    const controller = urlParams.get('c');
    const action = urlParams.get('a');
    
    // Đánh dấu menu item tương ứng
    const menuItems = document.querySelectorAll('.menu-dieu-huong a');
    menuItems.forEach(item => {
        const href = item.getAttribute('href');
        if (href && href.includes(`c=${controller}`) && href.includes(`a=${action}`)) {
            item.classList.add('active');
            // Mở submenu chứa item này nếu có
            const submenu = item.closest('.menu-con');
            if (submenu) {
                submenu.classList.add('hien-thi');
                const parentButton = document.querySelector(`[data-target="${submenu.id}"]`);
                if (parentButton) {
                    const icon = parentButton.querySelector('.icon-mo-dong');
                    if (icon) icon.classList.add('xoay');
                }
            }
        }
    });
}

/**
 * Xử lý loading states
 * 
 * Hiển thị loading indicator khi thực hiện các action
 */
function xuLyLoadingStates() {
    // Thêm loading state cho các link navigation
    const navigationLinks = document.querySelectorAll('.menu-dieu-huong a[href]:not([href="#"])');
    navigationLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Hiển thị loading indicator
            const icon = this.querySelector('i');
            if (icon) {
                const originalClass = icon.className;
                icon.className = 'fas fa-spinner fa-spin';
                
                // Khôi phục icon sau 2 giây (fallback)
                setTimeout(() => {
                    icon.className = originalClass;
                }, 2000);
            }
        });
    });
}

/**
 * Khởi tạo tất cả functionality khi DOM ready
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard JavaScript loaded');
    
    // Khởi tạo các chức năng
    xuLyDropdownMenu();
    khoiTaoTimKiem();
    xuLyResponsiveMenu();
    highlightMenuHienTai();
    xuLyLoadingStates();
    
    console.log('All dashboard features initialized');
});

/**
 * Utility functions
 */

/**
 * Đóng tất cả dropdown menus
 */
function dongTatCaDropdown() {
    document.querySelectorAll('.menu-con').forEach(menu => {
        menu.classList.remove('hien-thi');
    });
    document.querySelectorAll('.icon-mo-dong').forEach(icon => {
        icon.classList.remove('xoay');
    });
}

/**
 * Toggle sidebar (cho mobile)
 */
function toggleSidebar() {
    const sidebar = document.querySelector('.thanh-menu-ben');
    if (sidebar) {
        sidebar.classList.toggle('mo-mobile');
    }
}

/**
 * Export functions để có thể sử dụng từ bên ngoài
 */
window.Dashboard = {
    xuLyTimKiem,
    dongTatCaDropdown,
    toggleSidebar,
    highlightMenuHienTai
};