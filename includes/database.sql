CREATE DATABASE IF NOT EXISTS timetable_db;
USE timetable_db;

CREATE TABLE lecturers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100)
) ENGINE=InnoDB;

CREATE TABLE cohorts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  size INT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE halls (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  capacity INT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) NOT NULL,
  title VARCHAR(100) NOT NULL,
  lecturer_id INT NOT NULL,
  cohort_id INT NOT NULL,
  sessions_per_week INT DEFAULT 1,
  duration_hours INT DEFAULT 1,
  FOREIGN KEY (lecturer_id) REFERENCES lecturers(id),
  FOREIGN KEY (cohort_id) REFERENCES cohorts(id)
) ENGINE=InnoDB;

CREATE TABLE timetable (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  hall_id INT NOT NULL,
  day ENUM('Mon','Tue','Wed','Thu','Fri') NOT NULL,
  start_hour INT NOT NULL,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (hall_id) REFERENCES halls(id)
) ENGINE=InnoDB;

INSERT INTO halls (name, capacity) VALUES ('Hall A', 80), ('Hall B', 40);
INSERT INTO lecturers (name) VALUES ('Dr. Adeyemi'), ('Prof. Bello'), ('Dr. Musa