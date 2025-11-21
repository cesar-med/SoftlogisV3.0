-- =====================================
-- AUDITORIAS DATABASE
-- =====================================

CREATE DATABASE IF NOT EXISTS audits
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE audits;

CREATE TABLE audits.loggers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action ENUM('Insert','Update','Delete','Validate') NULL,
  module VARCHAR(255) NULL,
  data_json JSON NULL,
  reference TEXT NULL,
  message TEXT NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  view TINYINT(1) DEFAULT 0,
  view_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- SUBCATALOGS DATABASE
-- =====================================

CREATE DATABASE IF NOT EXISTS subcatalogs
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE subcatalogs;

CREATE TABLE offices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE work_shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contract_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE employee_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name ENUM('Administrative','Operational') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- RH DATABASE
-- =====================================

CREATE DATABASE IF NOT EXISTS rh
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE rh;

CREATE TABLE employees (
    id INT(11) NOT NULL AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL DEFAULT (UUID()),
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(500) NOT NULL,
    email VARCHAR(250) DEFAULT NULL,
    phone_number VARCHAR(20) DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    address_number VARCHAR(10) DEFAULT NULL,
    locality VARCHAR(250) DEFAULT NULL,
    city VARCHAR(250) DEFAULT NULL,
    state INT(11) DEFAULT NULL,
    date_of_joining DATE DEFAULT NULL,
    tax_id VARCHAR(20) DEFAULT NULL,
    personal_id VARCHAR(20) DEFAULT NULL,
    payroll_number INT(11) DEFAULT NULL,
    employee_type_id INT(11) DEFAULT NULL,
    work_shift_id INT(11) DEFAULT NULL,
    office_id INT(11) DEFAULT NULL,
    department_id INT(11) DEFAULT NULL,
    position_id INT(11) DEFAULT NULL,
    contract_type_id INT(11) DEFAULT NULL,
    salary FLOAT DEFAULT NULL,
    availability VARCHAR(45) DEFAULT 'Available',
    status TINYINT DEFAULT 1,
    created_by_user_id INT(11) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (employee_type_id) REFERENCES subcatalogs.employee_types(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (work_shift_id) REFERENCES subcatalogs.work_shifts(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (office_id) REFERENCES subcatalogs.offices(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (department_id) REFERENCES subcatalogs.departments(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (position_id) REFERENCES subcatalogs.positions(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (contract_type_id) REFERENCES subcatalogs.contract_types(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- AUTHENTICATE DATABASE
-- =====================================

CREATE DATABASE IF NOT EXISTS authenticate
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE authenticate;

CREATE TABLE roles (
    id INT(11) NOT NULL AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL DEFAULT (UUID()),
    description VARCHAR(45) DEFAULT NULL,
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by_user_id INT NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL DEFAULT (UUID()),
    employee_id INT(11) NOT NULL,
    role_id INT(11),
    email VARCHAR(45) NOT NULL,
    phone_number VARCHAR(35) NOT NULL,
    password VARCHAR(500) NOT NULL,
    token TEXT DEFAULT NULL,
    status TINYINT DEFAULT 1,
    created_by_user_id INT(11) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY phone_number_UNIQUE (phone_number),
    UNIQUE KEY email_UNIQUE (email),
    FOREIGN KEY (role_id) REFERENCES roles(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES rh.employees(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1020 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE modules (
    id INT(11) NOT NULL AUTO_INCREMENT,
    description VARCHAR(45) DEFAULT NULL,
    module_group VARCHAR(45) DEFAULT NULL,
    class TEXT DEFAULT NULL,
    path VARCHAR(255) DEFAULT NULL,
    status TINYINT DEFAULT 1,
    PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE operations (
    id INT(11) NOT NULL AUTO_INCREMENT,
    description VARCHAR(45) NOT NULL,
    module_id INT(11) NOT NULL,
    status TINYINT DEFAULT 1,
    PRIMARY KEY (id),
    FOREIGN KEY (module_id) REFERENCES modules(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=200 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE role_profile (
    id INT(11) NOT NULL AUTO_INCREMENT,
    role_id INT(11) NOT NULL,
    operation_id INT(11) NOT NULL,
    status TINYINT DEFAULT 1,
    PRIMARY KEY (id),
    FOREIGN KEY (role_id) REFERENCES roles(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (operation_id) REFERENCES operations(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sessions (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) DEFAULT NULL,
    token TEXT DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    validate_at DATETIME DEFAULT NULL,
    logout_at TIMESTAMP DEFAULT NULL,
    status TINYINT DEFAULT 1,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=100048 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================
-- INSERTS OPERATIONS
-- ===========================

USE authenticate; 

INSERT INTO operations (description, module_id) VALUES
('Ver', 100), ('Modificar', 100), ('Eliminar', 100), ('Exportar', 100),
('Ver', 101), ('Modificar', 101), ('Eliminar', 101), ('Exportar', 101),
('Ver', 102), ('Modificar', 102), ('Eliminar', 102), ('Exportar', 102),
('Ver', 103), ('Modificar', 103), ('Eliminar', 103), ('Exportar', 103),
('Ver', 104), ('Modificar', 104), ('Eliminar', 104), ('Exportar', 104),
('Ver', 105), ('Modificar', 105), ('Eliminar', 105), ('Exportar', 105),
('Ver', 106), ('Modificar', 106), ('Eliminar', 106), ('Exportar', 106),
('Ver', 107), ('Modificar', 107), ('Eliminar', 107), ('Exportar', 107),
('Ver', 108), ('Modificar', 108), ('Eliminar', 108), ('Exportar', 108),
('Ver', 109), ('Modificar', 109), ('Eliminar', 109), ('Exportar', 109),
('Ver', 110), ('Modificar', 110), ('Eliminar', 110), ('Exportar', 110),
('Ver', 111), ('Modificar', 111), ('Eliminar', 111), ('Exportar', 111),
('Ver', 112), ('Modificar', 112), ('Eliminar', 112), ('Exportar', 112),
('Ver', 113), ('Modificar', 113), ('Eliminar', 113), ('Exportar', 113),
('Ver', 114), ('Modificar', 114), ('Eliminar', 114), ('Exportar', 114),
('Ver', 115), ('Modificar', 115), ('Eliminar', 115), ('Exportar', 115),
('Ver', 116), ('Modificar', 116), ('Eliminar', 116), ('Exportar', 116),
('Ver', 117), ('Modificar', 117), ('Eliminar', 117), ('Exportar', 117),
('Ver', 118), ('Modificar', 118), ('Eliminar', 118), ('Exportar', 118),
('Ver', 119), ('Modificar', 119), ('Eliminar', 119), ('Exportar', 119),
('Ver', 120), ('Modificar', 120), ('Eliminar', 120), ('Exportar', 120),
('Ver', 121), ('Modificar', 121), ('Eliminar', 121), ('Exportar', 121),
('Ver', 122), ('Modificar', 122), ('Eliminar', 122), ('Exportar', 122),
('Ver', 123), ('Modificar', 123), ('Eliminar', 123), ('Exportar', 123),
('Ver', 124), ('Modificar', 124), ('Eliminar', 124), ('Exportar', 124),
('Ver', 125), ('Modificar', 125), ('Eliminar', 125), ('Exportar', 125),
('Ver', 126), ('Modificar', 126), ('Eliminar', 126), ('Exportar', 126),
('Ver', 127), ('Modificar', 127), ('Eliminar', 127), ('Exportar', 127),
('Ver', 128), ('Modificar', 128), ('Eliminar', 128), ('Exportar', 128),
('Ver', 129), ('Modificar', 129), ('Eliminar', 129), ('Exportar', 129),
('Ver', 130), ('Modificar', 130), ('Eliminar', 130), ('Exportar', 130);

-- ===========================
-- INSERT PROFILE POWER USER
-- ===========================

INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '200');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '201');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '202');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '203');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '204');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '205');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '206');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '207');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '208');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '209');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '210');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '211');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '212');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '213');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '214');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '215');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '216');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '217');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '218');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '219');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '220');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '221');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '222');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '223');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '224');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '225');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '226');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '227');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '228');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '229');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '230');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '231');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '232');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '233');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '234');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '235');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '236');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '237');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '238');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '239');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '240');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '241');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '242');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '243');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '244');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '245');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '246');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '247');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '248');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '249');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '250');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '251');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '252');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '253');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '254');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '255');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '256');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '257');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '258');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '259');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '260');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '261');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '262');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '263');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '264');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '265');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '266');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '267');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '268');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '269');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '270');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '271');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '272');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '273');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '274');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '275');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '276');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '277');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '278');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '279');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '280');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '281');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '282');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '283');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '284');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '285');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '286');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '287');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '288');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '289');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '290');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '291');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '292');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '293');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '294');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '295');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '296');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '297');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '298');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '299');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '300');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '301');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '302');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '303');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '304');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '305');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '306');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '307');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '308');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '309');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '310');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '311');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '312');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '313');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '314');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '315');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '316');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '317');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '318');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '319');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '320');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '321');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '322');
INSERT INTO `authenticate`.`role_profile` (`role_id`, `operation_id`) VALUES ('100', '323');


