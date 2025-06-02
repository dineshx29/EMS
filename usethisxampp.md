# XAMPP MySQL Setup for Employee Management System (EMS)

## Prerequisites
1. Start XAMPP
2. Start Apache and MySQL services
3. Open phpMyAdmin (http://localhost/phpmyadmin)

## Database Setup Commands

### 1. Create Database
```sql
CREATE DATABASE ems_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ems_database;
```

### 2. Create Tables

#### Roles Table
```sql
CREATE TABLE `roles` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `permissions` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Users Table
```sql
CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) UNSIGNED NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_username` (`username`),
  UNIQUE KEY `unique_email` (`email`),
  KEY `fk_users_role_id` (`role_id`),
  CONSTRAINT `fk_users_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Employees Table
```sql
CREATE TABLE `employees` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(20) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `hire_date` date NOT NULL,
  `department` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `manager_id` int(11) UNSIGNED DEFAULT NULL,
  `status` enum('active','inactive','terminated') DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_employee_id` (`employee_id`),
  UNIQUE KEY `unique_email` (`email`),
  KEY `idx_department` (`department`),
  KEY `idx_status` (`status`),
  KEY `fk_employees_manager_id` (`manager_id`),
  CONSTRAINT `fk_employees_manager_id` FOREIGN KEY (`manager_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3. Insert Initial Data

#### Insert Roles
```sql
INSERT INTO `roles` (`name`, `description`, `permissions`, `is_active`) VALUES
('Admin', 'System Administrator with full access', '["users.view", "users.create", "users.edit", "users.delete", "employees.view", "employees.create", "employees.edit", "employees.delete", "roles.view", "roles.create", "roles.edit", "roles.delete"]', 1),
('HR Manager', 'Human Resources Manager', '["employees.view", "employees.create", "employees.edit", "employees.delete", "users.view", "users.create", "users.edit"]', 1),
('Employee', 'Regular Employee', '["employees.view"]', 1);
```

#### Insert Users
```sql
INSERT INTO `users` (`username`, `email`, `password`, `role_id`, `is_active`) VALUES
('admin', 'admin@ems.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1),
('hr_manager', 'hr@ems.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 1),
('employee', 'employee@ems.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 1);
```

#### Insert Sample Employees
```sql
INSERT INTO `employees` (`employee_id`, `first_name`, `last_name`, `email`, `phone`, `address`, `date_of_birth`, `hire_date`, `department`, `position`, `salary`, `status`) VALUES
('EMP001', 'John', 'Doe', 'john.doe@company.com', '+1234567890', '123 Main St, City, State 12345', '1985-06-15', '2020-01-15', 'Engineering', 'Senior Developer', 75000.00, 'active'),
('EMP002', 'Jane', 'Smith', 'jane.smith@company.com', '+1234567891', '456 Oak Ave, City, State 12345', '1990-03-22', '2021-03-01', 'Marketing', 'Marketing Manager', 65000.00, 'active'),
('EMP003', 'Mike', 'Johnson', 'mike.johnson@company.com', '+1234567892', '789 Pine Rd, City, State 12345', '1988-11-08', '2019-09-15', 'Sales', 'Sales Representative', 55000.00, 'active'),
('EMP004', 'Sarah', 'Williams', 'sarah.williams@company.com', '+1234567893', '321 Elm St, City, State 12345', '1992-07-14', '2022-01-10', 'HR', 'HR Specialist', 60000.00, 'active'),
('EMP005', 'David', 'Brown', 'david.brown@company.com', '+1234567894', '654 Maple Dr, City, State 12345', '1987-04-03', '2018-11-20', 'Finance', 'Financial Analyst', 70000.00, 'active');
```

## Default Login Credentials

After running the above SQL commands, you can login with these credentials:

### Admin Account
- **Username:** `admin`
- **Email:** `admin@ems.com`
- **Password:** `password` (default password for all accounts)

### HR Manager Account
- **Username:** `hr_manager`
- **Email:** `hr@ems.com`
- **Password:** `password`

### Employee Account
- **Username:** `employee`
- **Email:** `employee@ems.com`
- **Password:** `password`

## Update Backend Configuration

After creating the database in XAMPP, update the `.env` file in the backend folder:

```env
database.default.hostname = localhost
database.default.database = ems_database
database.default.username = root
database.default.password = 
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306
```

## Testing the Setup

1. Start your CodeIgniter backend: `php spark serve --host=0.0.0.0 --port=8000`
2. Test the health endpoint: `curl http://localhost:8000/api/health`
3. Test login: 
   ```bash
   curl -X POST http://localhost:8000/api/auth/login \
   -H "Content-Type: application/json" \
   -d '{"login":"admin","password":"password"}'
   ```

## Important Notes

- The password hash `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi` corresponds to the password `password`
- Change all default passwords in production
- Make sure to enable JSON extension in PHP for the permissions field
- All tables use UTF-8 encoding for proper character support
- Foreign key constraints are enabled for data integrity
