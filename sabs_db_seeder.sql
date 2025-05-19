-- ─────────────────────────────────────────────────────────────────────────
-- 1) USERS: 30 students, 1 admin (id=4), 4 teachers
-- ─────────────────────────────────────────────────────────────────────────
INSERT INTO `user` (
  `user_id`,`user_key`,`password_hash`,`is_password_temporary`,`role`,`status`,
  `first_name`,`last_name`,`birthday`,`gender`,`created_at`
) VALUES
-- Students 1–3
( 1,'student1','password123',1,'student','active','Student','One','2005-01-01','female',NOW()),
( 2,'student2','password123',1,'student','active','Student','Two','2005-02-01','male',NOW()),
( 3,'student3','password123',1,'student','active','Student','Three','2005-03-01','other',NOW()),
-- Admin   4
( 4,'admin','password123',1,'admin','active','Super','Admin','1980-01-01','other',NOW()),
-- Students 5–31 (that makes 30 total students)
( 5,'student4','password123',1,'student','active','Student','Four','2005-04-01','female',NOW()),
( 6,'student5','password123',1,'student','active','Student','Five','2005-05-01','male',NOW()),
-- … repeat up through …
(31,'student30','password123',1,'student','active','Student','Thirty','2005-10-01','female',NOW()),
-- Teachers 32–35
(32,'teacher1','password123',1,'teacher','active','Teacher','One','1985-06-01','female',NOW()),
(33,'teacher2','password123',1,'teacher','active','Teacher','Two','1986-07-01','male',NOW()),
(34,'teacher3','password123',1,'teacher','active','Teacher','Three','1987-08-01','female',NOW()),
(35,'teacher4','password123',1,'teacher','active','Teacher','Four','1988-09-01','male',NOW());

-- ─────────────────────────────────────────────────────────────────────────
-- 2) ENROLLMENT TERMS (2 terms; term_id=1 active, term_id=2 inactive)
-- ─────────────────────────────────────────────────────────────────────────
INSERT INTO `enrollment_term` (
  `enrollment_term_id`,`academic_year`,`semester`,`term_start`,`term_end`,`status`
) VALUES
(1,'2024-2025','1st','2024-08-01','2024-12-15','active'),
(2,'2024-2025','2nd','2025-01-05','2025-05-20','inactive');

-- ─────────────────────────────────────────────────────────────────────────
-- 3) SUBJECTS (5)
-- ─────────────────────────────────────────────────────────────────────────
INSERT INTO `subject` (`subject_id`,`subject_code`,`subject_name`,`subject_credits`) VALUES
(1,'MATH101','Calculus I',3),
(2,'ENG102','English Literature',3),
(3,'SCI103','General Physics',4),
(4,'HIST104','World History',3),
(5,'CS105','Introduction to Programming',3);

-- ─────────────────────────────────────────────────────────────────────────
-- 4) CLASSES (15 total; 3 per subject: sections A, B, C)
-- ─────────────────────────────────────────────────────────────────────────
INSERT INTO `class` (`class_id`,`class_name`,`subject_id`,`section`) VALUES
-- Maths
( 1,'Calculus I - A',1,'A'),
( 2,'Calculus I - B',1,'B'),
( 3,'Calculus I - C',1,'C'),
-- English
( 4,'English Lit - A',2,'A'),
( 5,'English Lit - B',2,'B'),
( 6,'English Lit - C',2,'C'),
-- Physics
( 7,'Physics I - A',3,'A'),
( 8,'Physics I - B',3,'B'),
( 9,'Physics I - C',3,'C'),
-- History
(10,'World History - A',4,'A'),
(11,'World History - B',4,'B'),
(12,'World History - C',4,'C'),
-- Programming
(13,'Intro to Programming - A',5,'A'),
(14,'Intro to Programming - B',5,'B'),
(15,'Intro to Programming - C',5,'C');

-- ─────────────────────────────────────────────────────────────────────────
-- 5) TEACHER ASSIGNMENTS: each teacher → 3 random classes in term 1
-- ─────────────────────────────────────────────────────────────────────────
INSERT INTO `teacher_assignment` (`teacher_id`,`class_id`,`enrollment_term_id`) VALUES
(32, 2, 1),(32, 7, 1),(32,13, 1),
(33, 1, 1),(33, 5, 1),(33,12, 1),
(34, 3, 1),(34, 6, 1),(34,15, 1),
(35, 4, 1),(35, 8, 1),(35,14, 1);

-- ─────────────────────────────────────────────────────────────────────────
-- 6) STUDENT ASSIGNMENTS:
--    Each of the 30 students enrolls in exactly one section (A, B, or C)
--    for *each* of the 5 subjects (so 5 classes total), no duplicates.
--    We split 30 students evenly: IDs 1–10 → section A, 11–20 → B, 21–30 → C.
-- ─────────────────────────────────────────────────────────────────────────
INSERT INTO `student_assignment` (`student_id`,`class_id`,`enrollment_term_id`)
SELECT u.user_id, c.class_id, 1
FROM `user` u
JOIN `class` c ON c.subject_id BETWEEN 1 AND 5
WHERE u.role = 'student'
  AND (
       (u.user_id BETWEEN  1 AND 10 AND c.section = 'A')
    OR (u.user_id BETWEEN 11 AND 20 AND c.section = 'B')
    OR (u.user_id BETWEEN 21 AND 30 AND c.section = 'C')
  )
ORDER BY u.user_id, c.subject_id;
-- ─────────────────────────────────────────────────────────────────────────
-- 7) NOTIFICATIONS: one random notification per user
-- ─────────────────────────────────────────────────────────────────────────
INSERT INTO `notifications` (`user_id`,`message`,`type`)
SELECT u.user_id,
       CONCAT('You have a new update #', FLOOR(RAND()*1000)),
       ELT(FLOOR(RAND()*4)+1,'info','success','warning','error')
FROM `user` u;

-- ─────────────────────────────────────────────────────────────────────────
-- 8) CLASS SESSIONS: 2 per class (pending & active)
-- ─────────────────────────────────────────────────────────────────────────
INSERT INTO `class_session` (
  `class_session_name`,`class_id`,`open_datetime`,`close_datetime`,`status`
)
SELECT
  CONCAT('Session 1 for ', c.class_name), c.class_id,
  NOW() + INTERVAL (c.class_id) DAY,
  NOW() + INTERVAL (c.class_id)*1 DAY + INTERVAL 1 HOUR,
  'pending'
FROM `class` c
UNION ALL
SELECT
  CONCAT('Session 2 for ', c.class_name), c.class_id,
  NOW() + INTERVAL (c.class_id+15) DAY,
  NOW() + INTERVAL (c.class_id+15)*1 DAY + INTERVAL 1 HOUR,
  'active'
FROM `class` c;

-- ─────────────────────────────────────────────────────────────────────────
-- 9) ATTENDANCE: only for students with assignments in the relevant class
-- ─────────────────────────────────────────────────────────────────────────
INSERT INTO `attendance` (`user_id`,`class_session_id`,`status`,`is_manual`)
SELECT
  sa.student_id AS user_id,
  cs.class_session_id,
  ELT(FLOOR(RAND()*4)+1,'present','absent','late','unmarked'),
  FLOOR(RAND()*2)
FROM `student_assignment` sa
JOIN `class_session` cs ON cs.class_id = sa.class_id
WHERE sa.enrollment_term_id = 1;


-- ─────────────────────────────────────────────────────────────────────────
-- 10) LEAVE REQUESTS:
--     At most one request per student, total ~10 leaves.
--     We pick every third student (1,4,7,…) for a single random leave.
-- ─────────────────────────────────────────────────────────────────────────
INSERT INTO `attendance_leave` (`user_id`,`class_id`,`status`,`reason`,`leave_date`)
SELECT
  u.user_id,
  sa.class_id,
  'pending',
  CONCAT('Medical leave for class ', sa.class_id),
  CURDATE() - INTERVAL FLOOR(RAND()*15) DAY
FROM `user` u
JOIN `student_assignment` sa
  ON sa.student_id = u.user_id AND sa.enrollment_term_id = 1
WHERE u.role = 'student'
  AND (u.user_id % 3) = 1
LIMIT 10;

-- ─────────────────────────────────────────────────────────────────────────
-- 11) CREATE ROOMS (5)
-- ─────────────────────────────────────────────────────────────────────────
INSERT INTO `room` (`room_name`,`room_description`,`room_capacity`,`room_type`,`room_status`)
VALUES
  ('ITBR 101','Main lab','30','classroom','active'),
  ('ITBR 201','Second floor lab','30','classroom','active'),
  ('ITBR 301','Third floor lab','30','classroom','active'),
  ('ITBR 401','Fourth floor lab','30','classroom','active'),
  ('ITBR 501','Fifth floor lab','30','classroom','active');

-- ─────────────────────────────────────────────────────────────────────────
-- 12) CREATE SCHEDULES (15 entries; 1.5h each; Mon–Wed; 5 rooms; no overlap)
-- ─────────────────────────────────────────────────────────────────────────
INSERT INTO `schedule` (`room_id`,`class_id`,`time_start`,`time_end`,`week_day`,`status`)
VALUES
  -- Monday (classes 1–3)
  (1,  1,'08:00:00','09:30:00','mon','active'),
  (2,  2,'09:45:00','11:15:00','mon','active'),
  (3,  3,'11:30:00','13:00:00','mon','active'),

  -- Tuesday (classes 4–6)
  (4,  4,'08:30:00','10:00:00','tue','active'),
  (5,  5,'10:15:00','11:45:00','tue','active'),
  (1,  6,'13:15:00','14:45:00','tue','active'),

  -- Wednesday (classes 7–9)
  (2,  7,'07:30:00','09:00:00','wed','active'),
  (3,  8,'09:15:00','10:45:00','wed','active'),
  (4,  9,'12:00:00','13:30:00','wed','active'),

  -- Thursday (classes 10–12)
  (5, 10,'09:00:00','10:30:00','thu','active'),
  (1, 11,'10:45:00','12:15:00','thu','active'),
  (2, 12,'14:00:00','15:30:00','thu','active'),

  -- Friday (classes 13–15)
  (3, 13,'08:15:00','09:45:00','fri','active'),
  (4, 14,'10:30:00','12:00:00','fri','active'),
  (5, 15,'13:30:00','15:00:00','fri','active');