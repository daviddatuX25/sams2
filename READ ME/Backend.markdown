Attendance Management System Backend
This document outlines the backend design for the Student Attendance Management System (SAMS), which integrates ESP32 cameras, Compreface for facial recognition, and a PHP/MySQL backend built with the Slim framework. Below, all code is presented as pseudo code, followed by an enhanced developer guide.

Pseudo Code for Slim PHP API Implementations
1. activeClassSessions Endpoint
Checks if there is an active class session for a given tracker ID.

FUNCTION activeClassSessions(tracker_id):
    IF GET_CURRENT_CLASS_SESSION(tracker_id, now())
        RETURN {active: true }
    ENDIF
        RETURN {active: false }

ENDFUNCTION
Purpose: Returns a JSON object { "active": true/false } indicating whether a class session is active for the specified tracker_id.
Logic: Queries the class_sessions table to count sessions where the current time falls between open_datetime and close_datetime.
2. logAttendance Endpoint
Handles attendance logging from ESP32 trackers.

FUNCTION logAttendance(tracker_id, timestamp, action, photoFile):
    IF tracker_id is empty OR timestamp is empty OR action is empty OR photoFile is empty:
        RETURN {status: 'fail', message: 'Missing required parameters'} with status 400
    ENDIF
    TRY:
        studentId = COMPRE_FACE_RECOGNIZE(photoFile)
        IF studentId is null:
            RETURN {status: 'fail', message: 'No face detected or unrecognized'} with status 400
        ENDIF
        classSession = GET_CURRENT_CLASS_SESSION(tracker_id, timestamp)
        IF classSession is null:
            RETURN {status: 'fail', message: 'No active class session'} with status 404
        ENDIF
        IF NOT IS_STUDENT_PERMITTED(studentId, classSession):
            RETURN {status: 'fail', message: 'Student not permitted'} with status 403
        ENDIF
        DATABASE_BEGIN_TRANSACTION()
        existingLog = GET_ATTENDANCE_LOG(classSession.id, studentId, action)
        IF action == 'time_in':
            IF existingLog exists:
                studentName = GET_STUDENT_NAME(studentId)
                DATABASE_COMMIT()
                RETURN {status: 'fail', message: 'Time in already logged for ' + studentName} with status 409
            ENDIF
            CREATE_ATTENDANCE_LOG(classSession.id, studentId, tracker_id, 'time_in', timestamp)
            studentName = GET_STUDENT_NAME(studentId)
            DATABASE_COMMIT()
            RETURN {status: 'success', message: 'Time in logged for ' + studentName}
        ELSE IF action == 'time_out':
            timeInLog = GET_ATTENDANCE_LOG(classSession.id, studentId, 'time_in')
            IF timeInLog does not exist:
                DATABASE_COMMIT()
                RETURN {status: 'fail', message: 'No time in log found'} with status 404
            ENDIF
            IF existingLog exists:
                studentName = GET_STUDENT_NAME(studentId)
                DATABASE_COMMIT()
                RETURN {status: 'fail', message: 'Time out already logged for ' + studentName} with status 409
            ENDIF
            CREATE_ATTENDANCE_LOG(classSession.id, studentId, tracker_id, 'time_out', timestamp)
            studentName = GET_STUDENT_NAME(studentId)
            DATABASE_COMMIT()
            RETURN {status: 'success', message: 'Time out logged for ' + studentName}
        ELSE:
            DATABASE_COMMIT()
            RETURN {status: 'fail', message: 'Invalid action'} with status 400
        ENDIF
    CATCH Exception as error:
        DATABASE_ROLLBACK()
        RETURN {status: 'fail', message: 'Server error: ' + error.message} with status 500
    ENDTRY
ENDFUNCTION

-- helpers for slim api
FUNCTION GET_CURRENT_CLASS_SESSION(tracker_id, timestamp):
    datetime = CONVERT_TIMESTAMP_TO_DATETIME(timestamp)
    result = DATABASE_QUERY("SELECT * FROM class_sessions WHERE tracker_id = tracker_id AND open_datetime <= datetime AND close_datetime >= datetime")
    RETURN result.first()
ENDFUNCTION

FUNCTION IS_STUDENT_PERMITTED(studentId, classSession):
    count = DATABASE_QUERY("SELECT COUNT(*) as count FROM student_enrollment INNER JOIN class INNER JOIN classSession WHERE class_id = classSession.class_id AND student_enrollment.student_id = studentId").count
    RETURN count > 0
ENDFUNCTION

FUNCTION GET_ATTENDANCE_LOG(class_session_id, student_id, action):
    result = DATABASE_QUERY("SELECT * FROM attendance_logs WHERE class_session_id = class_session_id AND user_id = student_id AND action = action")
    RETURN result.first()
ENDFUNCTION


Non Slim API Functions
3. auto_marker Function
Cron job to mark attendance for recently ended sessions.

FUNCTION auto_marker():
    current_time = GET_CURRENT_TIME()
    FOR each session in GET_RECENTLY_ENDED_SESSIONS(current_time, window=1_minute):
        IF session.auto_mark_attendance == "yes":
            CALL auto_mark_attendance(session)
        ENDIF
    ENDFOR
ENDFUNCTION

4. Auto_mark_attendance Function
A helper -- Automatically marks attendance based on session thresholds.

FUNCTION auto_mark_attendance(class_session):
    SET time_in_threshold = class_session.time_in_threshold
    SET late_threshold = class_session.late_threshold
    SET time_out_threshold = class_session.time_out_threshold
    FOR each student in GET_ENROLLED_STUDENTS(class_session.class_id):
        time_in_log = GET_ATTENDANCE_LOG(class_session.id, student.id, 'time_in')
        time_out_log = GET_ATTENDANCE_LOG(class_session.id, student.id, 'time_out')
        IF time_in_log exists AND time_out_log exists:
            time_in_diff = CALCULATE_MINUTES_DIFFERENCE(time_in_log.timestamp, class_session.open_datetime)
            time_out_diff = CALCULATE_MINUTES_DIFFERENCE(class_session.close_datetime, time_out_log.timestamp)
            IF time_in_diff <= time_in_threshold AND time_out_diff <= time_out_threshold:
                UPSERT_ATTENDANCE(class_session.id, student.id, 'present')
            ELSE IF time_in_diff <= late_threshold:
                UPSERT_ATTENDANCE(class_session.id, student.id, 'late')
            ELSE:
                UPSERT_ATTENDANCE(class_session.id, student.id, 'absent')
            ENDIF
        ELSE:
            UPSERT_ATTENDANCE(class_session.id, student.id, 'absent')
        ENDIF
    ENDFOR
ENDFUNCTION

FUNCTION UPSERT_ATTENDANCE(class_session_id, student_id, status):
    existing = DATABASE_QUERY("SELECT * FROM attendance WHERE class_session_id = class_session_id AND user_id = student_id")
    IF existing exists:
        UPDATE_ATTENDANCE(existing.id, status)
    ELSE:
        CREATE_ATTENDANCE(class_session_id, student_id, status)
    ENDIF
ENDFUNCTION

5. auto_open_class_session Function
Cron job to create class sessions from schedules.

FUNCTION auto_open_class_session():
    current_time = GET_CURRENT_TIME()
    weekday = GET_WEEKDAY(current_time)
    FOR each schedule in GET_SCHEDULES(weekday, current_time):
        IF schedule.class.auto_create_session == "yes" AND NOT SESSION_EXISTS(schedule, current_time, tolerance=5_minutes):
            CREATE_CLASS_SESSION(
                schedule_id = schedule.id,
                tracker_id = schedule INNER JOIN room INNER JOIN trackers .tracker_id,
                open_datetime = schedule.start_time,
                close_datetime = schedule.end_time
            )
        ENDIF
    ENDFOR
ENDFUNCTION

----
Detailed Developer Guide
This developer guide provides a comprehensive overview of the SAMS backend, offering insights into its architecture, APIs, and operational flows to assist developers in implementation, maintenance, and extension.

1. System Overview
The Student Attendance Management System (SAMS) automates attendance tracking using:

ESP32 Cameras: Capture student photos and send data to the backend.
Compreface: Performs facial recognition to identify students.
Slim PHP Backend: Manages API requests, business logic, and database interactions.
MySQL Database: Stores session, attendance, and enrollment data.
Purpose: Streamline attendance logging and reporting for educational institutions.

2. Backend Architecture
Framework: Slim PHP, a lightweight micro-framework for building RESTful APIs.
Chosen for its simplicity, flexibility, and efficiency in handling HTTP requests.
Components:
API Endpoints: Handle requests from ESP32 devices.
Database: MySQL stores persistent data.
Cron Jobs: Automate session creation and attendance marking.
Database Schema (key tables):
class_sessions: id, tracker_id, open_datetime, close_datetime, class_id
attendance: id, class_session_id, student_id, time_in, time_out, status (present/late/absent)
student_enrollments: class_id, student_id
more....
3. API Endpoints
Detailed documentation for each endpoint, including pseudo code and examples.

GET /sams/api/activeClassSessions
Purpose: Check if an active class session exists for a tracker.
Parameters: tracker_id (query string)
Response: { "active": true/false }
Pseudo Code: See above.
Example Request:
GET /sams/api/activeClassSessions?tracker_id=123
Example Response (Active):
{ "active": true }
Example Response (Inactive):
{ "active": false }


POST /sams/api/logAttendance
Purpose: Log student attendance (time-in or time-out).
Parameters: tracker_id, timestamp, action (time_in/time_out), photoFile (multipart/form-data)
Response: { "status": "success/fail", "message": "..." }
Pseudo Code: See above.
Example Request:
POST /sams/api/logAttendance
Content-Type: multipart/form-data
Body: tracker_id=123&timestamp=1623456789000&action=time_in&photoFile=[binary]
Example Response (Success):
{ "status": "success", "message": "Time in recorded for John Doe" }
Example Response (Failure):
{ "status": "fail", "message": "No active class session" }


4. Helper Functions
GET_CURRENT_CLASS_SESSION: Retrieves the current session based on tracker_id and timestamp.
IS_STUDENT_PERMITTED: Verifies student enrollment in the class.
Pseudo Code: Included in logAttendance section.
5. Auto Processes
Auto Marker:
Runs every 60 seconds via cron.
Marks attendance for sessions ended within the last minute.
Pseudo code: See auto_marker and auto_mark_attendance.
Auto Open:
Runs every 60 seconds via cron.
Creates sessions based on schedules if none exist.
Pseudo code: See auto_open_class_session.
Cron Setup:
* * * * * php /path/to/script.php auto_marker
* * * * * php /path/to/script.php auto_open_class_session

6. Integration with Compreface
Function: COMPRE_FACE_RECOGNIZE(photoFile) (assumed wrapper).
Process: Sends photoFile to Compreface API, returns studentId or null if unrecognized.
Note: Ensure Compreface API key and endpoint are configured.

7. Database Interactions
Queries: Used in endpoints and functions (e.g., session checks, enrollment verification).
Transactions: Employed in logAttendance to ensure data integrity.
Indexing: Add indexes on tracker_id, student_id, class_id for performance.

8. Error Handling
Validation: Missing parameters return 400 status.
Recognition Failure: 400 if no face detected.
Session/Enrollment Issues: 404 or 403 as appropriate.
Duplicates: 409 for repeated time-in/out.
Server Errors: 500 with rollback on exceptions.

9. Security Considerations
HTTPS: Encrypt all communications.
Input Validation: Prevent injection attacks.
Authentication: Consider API keys or tokens for tracker authentication.
Time Sync: Ensure ESP32 and server clocks are synchronized.

10. Scalability and Performance
Database: Optimize queries with indexes; use caching for frequent reads.
Cron Jobs: Monitor load; adjust frequency if needed.
Load Balancing: Scale Slim servers as tracker count grows.

11. ESP32Cam Integration
Compatibility: ESP32 code queries activeClassSessions and posts to logAttendance.
Alignment: No changes needed; matches backend API design.

12. Deployment
Slim PHP Setup:
Install via Composer: composer require slim/slim "^4.0".
Configure routes in index.php.
Set up PDO for MySQL connection.
Database:
Run SQL scripts to create tables.
Example: CREATE TABLE class_sessions (id INT PRIMARY KEY, tracker_id VARCHAR(50), ...);
Web Server: Configure Apache/Nginx to serve Slim app.

13. Testing
Tools: Use Postman to test APIs.
Unit Tests: Write tests for helper functions.
Scenarios: Test success, missing parameters, and error cases.

14. Extending the System
New Endpoints: Follow Slim routing patterns; add pseudo code first.
Features: E.g., attendance reports via new API.
Best Practices: Modularize code, document changes.

---
System Backend Diagram
[ESP32 Tracker] --> HTTPS GET /activeClassSessions --> [Slim PHP Server]
    |                        |
    |                        |--> [MySQL: class_sessions]
    |                        |
    |<-- {active: true/false} <--|

[ESP32 Tracker] --> HTTPS POST /logAttendance --> [Slim PHP Server]
    |                        |
    |                        |--> [Compreface API]
    |                        |--> [MySQL: class_sessions, attendance]
    |                        |
    |<-- {status, message} <--|

[Slim PHP Server] --> Cron --> [auto_marker]
                  --> Cron --> [auto_open_class_session]


Backend Flow
Active Class Sessions: ESP32 queries endpoint; server checks database and responds.
Attendance Logging: ESP32 posts data; server validates, recognizes, updates records, and responds.
Auto Processes: Cron jobs manage session creation and attendance marking.
Additional Notes
Assumptions: Database tables exist with expected fields.
Scalability: Optimize as system scales.
ESP32 Logic: Fully compatible with backend.
