CREATE TABLE `admin` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `updationDate` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE patient_account (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  province VARCHAR(100) NOT NULL,
  city VARCHAR(100) NOT NULL,
  barangay VARCHAR(100) NOT NULL,
  contact_no VARCHAR(20) NOT NULL,
  birthdate DATE NOT NULL,
  age INT NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  creationdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reset_token VARCHAR(255),
  reset_token_expiry DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `obgyn` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) DEFAULT NULL,
  province VARCHAR(100) NOT NULL,
  city VARCHAR(100) NOT NULL,
  barangay VARCHAR(100) NOT NULL,
  `contactno` bigint DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `creationDate` timestamp NULL DEFAULT current_timestamp(),
  `updationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `midwife` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) DEFAULT NULL,
  province VARCHAR(100) NOT NULL,
  city VARCHAR(100) NOT NULL,
  barangay VARCHAR(100) NOT NULL,
  `contactno` bigint DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `creationDate` timestamp NULL DEFAULT current_timestamp(),
  `updationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `secretary` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) DEFAULT NULL,
  province VARCHAR(100) NOT NULL,
  city VARCHAR(100) NOT NULL,
  barangay VARCHAR(100) NOT NULL,
  `contactno` bigint DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `creationDate` timestamp NULL DEFAULT current_timestamp(),
  `updationDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE patient_record (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pat_id VARCHAR(255) DEFAULT NULL,
  midwife_id INT DEFAULT NULL,  
  obgyn_id INT DEFAULT NULL, 
  patient_account_id INT DEFAULT NULL,   
  patient_name VARCHAR(255) NOT NULL,
  province VARCHAR(100) NOT NULL,
  city VARCHAR(100) NOT NULL,
  barangay VARCHAR(100) NOT NULL,
  contact_no VARCHAR(20) NOT NULL,
  email VARCHAR(255) UNIQUE,
  birthdate DATE NOT NULL,
  creationdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updationdate TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (midwife_id) REFERENCES midwife(id) ON DELETE SET NULL,
  FOREIGN KEY (patient_account_id) REFERENCES patient_account(id) ON DELETE SET NULL,
  FOREIGN KEY (obgyn_id) REFERENCES obgyn(id) ON DELETE SET NULL,
  UNIQUE (patient_name, contact_no, email)     
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_account_id INT NOT NULL, 
  patient_record_id INT,   
  obgyn_id INT DEFAULT NULL,
  midwife_id INT DEFAULT NULL,        
  message longtext,       
  preferred_date DATE NOT NULL,
  preferred_time TIME NOT NULL,
  status ENUM('Pending', 'Approved', 'Rejected', 'Completed', 'Referred') DEFAULT 'Pending',
  admin_remarks TEXT,
  creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  update_date TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_account_id) REFERENCES patient_account(id),
  FOREIGN KEY (patient_record_id) REFERENCES patient_record(id),
  FOREIGN KEY (obgyn_id) REFERENCES obgyn(id),
  FOREIGN KEY (midwife_id) REFERENCES midwife(id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE availability (
  id INT AUTO_INCREMENT PRIMARY KEY,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  slots TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE gen_consultation (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_record_id INT NOT NULL,
  date DATE NOT NULL,
  reason_for_visit TEXT NOT NULL,
  FOREIGN KEY (patient_record_id) REFERENCES patient_record(id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE services (
  service_id INT AUTO_INCREMENT PRIMARY KEY,
  service_name VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE fields (
  field_id INT AUTO_INCREMENT PRIMARY KEY,
  service_id INT,
  field_name VARCHAR(255) NOT NULL,
  field_type ENUM('text', 'number', 'date', 'select', 'textarea') NOT NULL,
  options TEXT, 
  FOREIGN KEY (service_id) REFERENCES services(service_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE patient_service_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_record_id INT NOT NULL,
  service_id INT NOT NULL,
  field_id INT NOT NULL,
  field_value TEXT NOT NULL, 
  date_served DATE NOT NULL,
  FOREIGN KEY (patient_record_id) REFERENCES patient_record(id),
  FOREIGN KEY (service_id) REFERENCES services(service_id),
  FOREIGN KEY (field_id) REFERENCES fields(field_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `services`(`service_name`) VALUES ('Transvaginal Ultrasound'), ('Pelvic Ultrasound'), ('BPS with NST'), ('Pap Smear'), ('Pregnancy Test'), ('IUD Insertion'), ('IUD Removal'), ('Implant Insertion'), ('Implant Removal'), ('Flu Vaccine'), ('DMPA'), ('Cervarix'), ('Gardasil'), ('OB Assisted Delivery'), ('Midwife Assisted Delivery');

-- Transvaginal Ultrasound Fields
INSERT INTO fields (service_id, field_name, field_type, options) VALUES
('1', 'Pregnant?', 'select', 'Yes,No'),
('1', 'Notes', 'textarea', NULL);

-- Pelvic Ultrasound Fields
INSERT INTO fields (service_id, field_name, field_type, options) VALUES
('2', 'Notes', 'textarea', NULL);

-- BPS with NST Fields
INSERT INTO fields (service_id, field_name, field_type, options) VALUES
('3', 'Estimated Due Date', 'date', NULL),
('3', 'Fetal heartbeat per bpm', 'text', NULL),
('3', 'Weeks of Gestation', 'text', NULL),
('3', 'Notes', 'textarea', NULL);

-- Pap Smear Fields
INSERT INTO fields (service_id, field_name, field_type, options) VALUES
('4', 'Menstrual Cycle', 'textarea', NULL),
('4', 'Sex Contact', 'date', NULL),
('4', 'Notes', 'textarea', NULL);

-- Pregnancy Test Fields
INSERT INTO fields (service_id, field_name, field_type, options) VALUES
('5', 'Method', 'select', 'Urine,Blood'),
('5', 'Result', 'select', 'Positive,Negative'),
('5', 'Notes', 'textarea', NULL);

-- IUD Insertion Fields
INSERT INTO fields (service_id, field_name, field_type, options) VALUES
('6', 'First Day of Menstruation', 'date', NULL),
('6', 'Last Day of Menstruation', 'date', NULL),
('6', 'Date of Insertion', 'date', NULL),
('6', 'Notes', 'textarea', NULL);

-- IUD Removal Fields
INSERT INTO fields (service_id, field_name, field_type, options) VALUES
('7', 'First Day of Menstruation', 'date', NULL),
('7', 'Last Day of Menstruation', 'date', NULL),
('7', 'Date of Removal', 'date', NULL),
('7', 'Reason for Removal', 'textarea', NULL),
('7', 'Notes', 'textarea', NULL);

-- Implant Insertion Fields
INSERT INTO fields (service_id, field_name, field_type, options) VALUES
('8', 'History of first contraceptive', 'textarea', NULL),
('8', 'Notes', 'textarea', NULL);

-- Implant Removal Fields
INSERT INTO fields (service_id, field_name, field_type, options) VALUES
('9', 'History of first contraceptive', 'textarea', NULL),
('9', 'Notes', 'textarea', NULL);

-- Flu Vaccine Fields
INSERT INTO fields (service_id, field_name, field_type, options) VALUES
('10', 'Allergic Reactions?', 'textarea', NULL),
('10', 'First Dose', 'date', NULL),
('10', 'Last Dose', 'date', NULL),
('10', 'Notes', 'textarea', NULL);

-- DMPA Fields
INSERT INTO fields (service_id, field_name, field_type, options) VALUES
('11', 'Month Selected', 'select', 'Monthly (Norifam),3 months (Depo Provera)'),
('11', 'Last Inject Date', 'date', NULL),
('11', 'Next Due Date', 'date', NULL),
('11', 'Side Effects(if any)', 'textarea', NULL);

-- Cervarix Fields
INSERT INTO fields (service_id, field_name, field_type, options) VALUES
('12', 'Dose', 'select', 'First Dose,Second Dose,Third Dose'),
('12', 'Next Dose Due', 'date', NULL),
('12', 'Notes', 'textarea', NULL);

-- Gardasil Fields
INSERT INTO fields (service_id, field_name, field_type, options) VALUES
('13', 'Gardasil Type', 'select', 'Gardasil 4,Gardasil 9'),
('13', 'Date Given', 'date', NULL),
('13', 'Next Dose Due', 'date', NULL),
('13', 'Notes', 'textarea', NULL);

-- OB Assisted Delivery Fields
INSERT INTO fields (service_id, field_name, field_type, options) VALUES
('14', 'Delivery Date', 'date', NULL),
('14', 'Pregnancy Outcome', 'select', 'Livebirth,Stillbirth,Miscarriage'),
('14', 'Gender of Baby', 'select', 'Male,Female'),
('14', 'Birth Weight', 'text', NULL),
('14', 'Notes', 'textarea', NULL);

-- Midwife Assisted Delivery Fields
INSERT INTO fields (service_id, field_name, field_type, options) VALUES
('15', 'Delivery Date', 'date', NULL),
('15', 'Pregnancy Outcome', 'select', 'Livebirth,Stillbirth,Miscarriage'),
('15', 'Gender of Baby', 'select', 'Male,Female'),
('15', 'Birth Weight', 'text', NULL),
('15', 'Notes', 'textarea', NULL);

CREATE TABLE user_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  obgyn_id INT DEFAULT NULL,
  midwife_id INT DEFAULT NULL,
  secretary_id INT DEFAULT NULL,
  role ENUM('OBGYNE', 'Midwife', 'Secretary') NOT NULL,
  username VARCHAR(255) NOT NULL,
  login TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  logout TIMESTAMP NULL DEFAULT NULL,
  CHECK ((role = 'OBGYNE' AND obgyn_id IS NOT NULL) OR (role = 'Midwife' AND midwife_id IS NOT NULL) OR (role = 'Secretary' AND secretary_id IS NOT NULL)),
  FOREIGN KEY (obgyn_id) REFERENCES obgyn(id),
  FOREIGN KEY (midwife_id) REFERENCES midwife(id),
  FOREIGN KEY (secretary_id) REFERENCES secretary(id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE announcements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  message TEXT NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  end_time TIME NOT NULL DEFAULT '23:59', 
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  image VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;