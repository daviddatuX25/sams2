# UI/UX Description for Attendance Management System

The Attendance Management System provides role-based interfaces for **students**, **teachers**, and **admins**, each with a customized sidebar that organizes the pages and features specific to their needs. The design is clean, responsive, and consistent across roles, ensuring ease of use on both desktop and mobile devices.

## General Layout
- **Sidebar/Topbar Navigation**: A fixed or collapsible sidebar on the left for quick access to main features.
- **Top Navigation Bar**: Displays the user's name, role (e.g., student, teacher, admin), and options like logout, profile settings, and help.
- **Main Content Area**: Displays the selected page or subpage, with tabs or sections for detailed views.
- **Responsive Design**: Adapts to different screen sizes, ensuring usability on mobile devices.

---

## 1. Student Interface
Students can view their classes, attendance history, subjects, teachers, schedule, and manage leave requests.

### Navbar structure
- **Dashboard**
  - Overview of upcoming class sessions, recent attendance status, and notifications.
- **My Classes**
  - List of enrolled classes (from `student_assignment` and `class` tables).
 
  - **Subpage** (Inside a Class): 
    - View Classmates, clas teacher, subject, attendance data (late, absent, present) count
      *Class Sessions* – View past and upcoming sessions (`class_sessions`) with attendance status (`attendance`). Give link to redirect.
- **Attendance Logs**
  - Show attendance logs
  - Initial view get by recent date
  - Filters: date range, class name, status, etc.
- **Subjects**
  - List of subjects (`subject`) linked to enrolled classes, with descriptions.
- **Teachers**
  - Information about teachers (`users` with role 'teacher') assigned to their classes (`teacher_assignment`).
- **Schedule**
  - Weekly or monthly view of class schedules (`schedule` table).
- **Leave Requests**
  - Form to submit new leave requests (`attendance_leave`).
  - View status of previous requests (pending, approved, rejected).
 **Profile**
  - Update bio, gender, etc.
### Key Features
- **Class Sessions**: Displays session details (e.g., `open_datetime`, `close_datetime`) and attendance status.
- **Attendance History**: Interactive calendar with filters by term (`enrollment_term`).
- **Leave Requests**: Form includes fields for `letter` and shows `status` updates.

---

## 2. Teacher Interface
Teachers can manage class sessions, mark attendance, view reports, and access information about their classes and students.

### Sidebar Structure
- **Dashboard**
  - Overview of today's classes (`schedule`), upcoming sessions (`class_sessions`), and pending tasks (e.g., marking attendance).
- **My Classes**
  - List of classes taught (`teacher_assignment` and `class` tables).
  - **Subpages**:
    - *Class Sessions* – Create, edit, cancel sessions (`class_sessions`), and view details. Select class session settings or
    - *Students* – List of enrolled students (`student_assignment`) with options to view individual attendance (`attendance`).
    - *Attendance Reports* – Generate and view attendance statistics for the class.
    - *Class Settings* - attendance_method, auto mark attendance, auto open class sessions, CRUD class_session_settings 
- **Schedule**
  - Teaching schedule (`schedule`) showing class times, rooms, and days.
- **Subjects**
  - Information about subjects taught (`subject` linked via `class`).
- **Students**
  - General list of all students across classes, with search functionality.
- **Notifications**
  - Alerts for leave requests (`attendance_leave`), session updates, etc. (`notifications`).

### Key Features
- **Class Management**: Tabs for sessions, students, reports and class and class_session settings within "My Classes."
- **Attendance Marking**: Manual option to update `status` in `attendance` (e.g., present, absent, late).
- **Reports**: Visual summaries of attendance data, filterable by class or term.

---

## 3. Admin Interface
Admins have comprehensive access to manage users, classes, rooms, subjects, trackers, and system settings.

### Sidebar Structure
- **Dashboard**
  - System-wide analytics (e.g., attendance rates), recent activities, and pending tasks (e.g., user approvals).
- **User Management**
  - Add, edit, delete users (`users` table).
  - **Subpages**:
    - *Students* – Manage student records and enrollments (`student_assignment`).
    - *Teachers* – Manage teacher records and assignments (`teacher_assignment`).
    - *Admins* – Manage admin users.
- **Class Management**
  - Oversee all classes (`class`), assign teachers, and manage enrollments.
- **Room Management**
  - Add, edit, delete rooms (`rooms`), and assign trackers (`trackers`).
- **Subject Management**
  - Manage subjects (`subject`) and link to classes.
- **Tracker Management**
  - Configure trackers (`trackers`) and link to rooms.
- **Enrollment Terms**
  - Manage academic terms and semesters (`enrollment_term`).
- **Facial Recognition**
  - Upload and manage student photos (`profile_picture` in `users`), link to `user_id`, and test recognition.
- **Reports**
  - Generate system-wide reports on attendance (`attendance`), user activity, etc.
- **Settings**
  - Configure system settings (e.g., `time_in_threshold`, `late_threshold` in `class_sessions`).

### Key Features
- **User Management**: Detailed control over user roles and statuses (active, pending, archived).
- **Facial Recognition**: Interface to upload photos and verify recognition accuracy.
- **System Settings**: Centralized default configuration for attendance rules and automation (`class_session_settings`).

---

## Additional UI/UX Considerations
- **Consistent Design**: Uniform icons, colors, and fonts across all interfaces for actions like "edit," "delete," and "save."
- **Subpage Navigation**: Tabs or dropdowns within main pages (e.g., "My Classes") to access subpages without leaving context.
- **Search and Filters**: Search bars and filters in lists (e.g., students, classes) for quick access.
- **Notifications**: Display in a sidebar or popup (`notifications` table), with options to mark as read.
- **Mobile Optimization**: Sidebars collapse or transform into a menu on smaller screens.

---

## Example Interaction Flow
- **Student**: Logs in → Sees dashboard with upcoming sessions → Clicks "My Classes" → Selects a class → Views "Class Sessions" tab to check attendance.
- **Teacher**: Logs in → Checks dashboard for today’s schedule → Goes to "My Classes" → Selects a class → Marks attendance in "Class Sessions" tab.
- **Admin**: Logs in → Reviews dashboard analytics → Navigates to "User Management" → Edits a student’s enrollment or uploads a photo in "Facial Recognition."

This structure ensures that each role has a tailored interface with easy access to relevant features, enhancing usability and efficiency across the system.