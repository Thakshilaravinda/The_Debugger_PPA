-- Create Database
CREATE DATABASE IF NOT EXISTS PPA_Transport
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;

-- Use Database
USE PPA_Transport;

-- Table: drivers
CREATE TABLE drivers (
  driver_id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  license_no VARCHAR(50) DEFAULT NULL,
  address VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (driver_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: vehicles
CREATE TABLE vehicles (
  vehicle_id INT(11) NOT NULL AUTO_INCREMENT,
  registration_no VARCHAR(50) NOT NULL,
  model VARCHAR(100) DEFAULT NULL,
  type VARCHAR(50) DEFAULT NULL,
  capacity VARCHAR(50) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (vehicle_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: petrol_bills
CREATE TABLE petrol_bills (
  bill_id INT(11) NOT NULL AUTO_INCREMENT,
  vehicle_id INT(11) DEFAULT NULL,
  driver_id INT(11) DEFAULT NULL,
  date DATE NOT NULL,
  liters DECIMAL(10,2) DEFAULT NULL,
  amount DECIMAL(10,2) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (bill_id),
  KEY vehicle_id (vehicle_id),
  KEY driver_id (driver_id),
  CONSTRAINT petrol_bills_ibfk_1 FOREIGN KEY (vehicle_id) 
      REFERENCES vehicles (vehicle_id) ON DELETE SET NULL,
  CONSTRAINT petrol_bills_ibfk_2 FOREIGN KEY (driver_id) 
      REFERENCES drivers (driver_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: vehicle_services
CREATE TABLE vehicle_services (
  service_id INT(11) NOT NULL AUTO_INCREMENT,
  vehicle_id INT(11) DEFAULT NULL,
  service_type VARCHAR(100) DEFAULT NULL,
  service_date DATE DEFAULT NULL,
  cost DECIMAL(10,2) DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (service_id),
  KEY vehicle_id (vehicle_id),
  CONSTRAINT vehicle_services_ibfk_1 FOREIGN KEY (vehicle_id) 
      REFERENCES vehicles (vehicle_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
