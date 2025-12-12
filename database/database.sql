
CREATE TABLE faculty (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    password VARCHAR(255),
    qualification VARCHAR(100),
    experience INT,
    role VARCHAR(20) DEFAULT 'faculty'
);

CREATE TABLE promotion_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id INT,
    api_score INT,
    document VARCHAR(255),
    status VARCHAR(20) DEFAULT 'Pending',
    applied_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
