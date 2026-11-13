-- ============================================================
-- Faculty Evaluation System - database structure + sample data
-- Import this file into an EMPTY database using phpMyAdmin.
--   Local (XAMPP):  create database `faculty_eval_db`, then Import
--   InfinityFree:   select the database you created, then Import
-- (No CREATE DATABASE line on purpose: InfinityFree does not allow it.)
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS evaluations;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS faculty;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Table: faculty  (teachers who get evaluated)
-- ------------------------------------------------------------
CREATE TABLE faculty (
  faculty_id  INT AUTO_INCREMENT PRIMARY KEY,
  full_name   VARCHAR(100) NOT NULL,
  department  VARCHAR(100) NOT NULL,
  email       VARCHAR(100) NOT NULL,
  position    VARCHAR(50)  DEFAULT NULL,
  date_added  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: students  (people who submit evaluations)
-- ------------------------------------------------------------
CREATE TABLE students (
  student_id  INT AUTO_INCREMENT PRIMARY KEY,
  student_no  VARCHAR(20)  NOT NULL UNIQUE,
  full_name   VARCHAR(100) NOT NULL,
  course      VARCHAR(50)  DEFAULT NULL,
  year_level  TINYINT      DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: evaluations  (one row = one student rating one faculty)
-- Each rating is 1 (Poor) to 5 (Excellent).
-- Deleting a faculty or student also deletes their evaluations.
-- ------------------------------------------------------------
CREATE TABLE evaluations (
  eval_id          INT AUTO_INCREMENT PRIMARY KEY,
  faculty_id       INT          NOT NULL,
  student_id       INT          NOT NULL,
  subject          VARCHAR(100) NOT NULL,
  teaching_skill   TINYINT      NOT NULL,
  communication    TINYINT      NOT NULL,
  punctuality      TINYINT      NOT NULL,
  professionalism  TINYINT      NOT NULL,
  comments         TEXT         DEFAULT NULL,
  date_evaluated   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_eval_faculty FOREIGN KEY (faculty_id)
    REFERENCES faculty(faculty_id) ON DELETE CASCADE,
  CONSTRAINT fk_eval_student FOREIGN KEY (student_id)
    REFERENCES students(student_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Sample data (safe to delete after testing)
-- ------------------------------------------------------------
INSERT INTO faculty (full_name, department, email, position) VALUES
('Maria Santos',   'Information Technology', 'maria.santos@example.edu',   'Associate Professor'),
('Juan Dela Cruz', 'Computer Science',       'juan.delacruz@example.edu',  'Instructor'),
('Ana Reyes',      'Mathematics',            'ana.reyes@example.edu',      'Assistant Professor'),
('Pedro Garcia',   'Information Technology', 'pedro.garcia@example.edu',   'Instructor');

INSERT INTO students (student_no, full_name, course, year_level) VALUES
('2023-0001', 'Liza Mendoza',   'BSIT', 3),
('2023-0002', 'Carlo Ramos',    'BSIT', 3),
('2024-0105', 'Bea Villanueva', 'BSCS', 2),
('2024-0210', 'Mark Aquino',    'BSIT', 2);

INSERT INTO evaluations
  (faculty_id, student_id, subject, teaching_skill, communication, punctuality, professionalism, comments)
VALUES
(1, 1, 'Web Systems Technologies', 5, 5, 4, 5, 'Explains PHP and MySQL very clearly.'),
(1, 2, 'Web Systems Technologies', 4, 5, 5, 5, 'Always prepared and approachable.'),
(2, 1, 'Data Structures',          4, 3, 4, 4, 'Good lessons, but could slow down a bit.'),
(2, 3, 'Data Structures',          3, 4, 3, 4, NULL),
(3, 3, 'Discrete Mathematics',     5, 4, 5, 5, 'Very patient with questions.'),
(4, 4, 'Networking Fundamentals',  4, 4, 2, 4, 'Often starts class late.');
