

USE employee_system;

CREATE TABLE employees (
    employee_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'employee',
    designation VARCHAR(255),
    qualification VARCHAR(255),
    address TEXT,
    contact_number VARCHAR(15),
    resume VARCHAR(255),
    degree_certificate VARCHAR(255),
    aadhaar_card VARCHAR(255),
    pan_card VARCHAR(255),
    live_image VARCHAR(255),
    bank_passbook VARCHAR(255),
    attendance ENUM('Present', 'Absent') DEFAULT 'Absent',
    last_login DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_logout DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;




CREATE TABLE pending_employees (
    employee_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'employee',
    designation VARCHAR(255),
    qualification VARCHAR(255),
    address TEXT,
    contact_number VARCHAR(15),
    resume VARCHAR(255),
    degree_certificate VARCHAR(255),
    aadhaar_card VARCHAR(255),
    pan_card VARCHAR(255),
    live_image VARCHAR(255),          -- Column to store live image path
    bank_passbook VARCHAR(255)  -- Column to store bank passbook path

)ENGINE=InnoDB;



CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    task_description TEXT NOT NULL,
    status ENUM('pending', 'completed') DEFAULT 'pending',
    due_date DATE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id)
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE,
    task_id INT,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    FOREIGN KEY (task_id) REFERENCES tasks(id)  -- Assuming 'id' is the primary key in tasks table
);

CREATE TABLE tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL,
    employee_id INT NOT NULL,
    expiry DATETIME NOT NULL
);

CREATE TABLE work_reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    username VARCHAR(100) NOT NULL,
    report_file VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id)
) ENGINE=InnoDB;


UPDATE work_reports wr
JOIN employees e ON wr.employee_id = e.employee_id
SET wr.username = e.username;
 

 
 ALTER TABLE work_reports
ADD description TEXT NOT NULL;