CREATE DATABASE IF NOT EXISTS laravel_test_db;
GRANT ALL PRIVILEGES ON laravel_test_db.* TO 'laravel_user'@'%';
FLUSH PRIVILEGES;