Chuẩn bị:
Php >= 8 (tải xampp để có mysql và tạo sẵn db tên web-booking)

Các bước chạy project:

B1: git clone
B2: Vào project mở Terminal chạy lệnh: composer install
B3: Tạo file .env rồi copy nội dung trong file .env.example
B4: Chạy lệnh: php artisan migrate --path=database/migrations/
B5: Mở Mysql để chạy thêm nội dung Query trong file query_token.txt
B6: Chạy lệnh: php artisan db:seed --class=RoleSeeder 
	Và chạy tiếp lệnh: php artisan db:seed --class=UserSeeder
B7: Chạy lệnh: php artisan serve

Done

Có thể insert DB nếu có thêm file SQL để có dữ liệu