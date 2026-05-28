# EXECUTIVE SUMMARY: Teacher Dashboard & Database Architecture

## Overview

This document summarizes the architecture, data flow, and critical issues in the teacher/student evaluation system of the FEFS (Faculty Evaluation Feedback System).

---

## Key Findings

### ✅ What Works Well

1. **Student-to-Teacher Evaluation** - Students can evaluate their teachers by course/year/section matching
2. **Teacher-to-Teacher Evaluation** - Teachers can evaluate co-teachers based on shared subjects
3. **View Students** - Teachers can see enrolled students for their classes
4. **Access Control** - Proper authentication and role-based access for teachers/students/admins
5. **Question Management** - Dynamic questions for student and faculty evaluations

### ❌ Critical Issues

1. **MISSING class_assignments TABLE DEFINITION** - Heavily used but not in setup.sql
2. **BROKEN Student Count** - Shows 0 students in "My Subjects" page
3. **Data Inconsistency** - Student data in both users table AND student_details table
4. **Unused Code** - teacher_subjects and student_subjects tables created but never populated

---

## Data Architecture

### Core Relationship

```
Teacher → class_assignments ← Students (by course/year/section)
           ↓
        Subjects
           ↓
        Evaluations (incoming from students/teachers)
```

### Key Tables

| Table | Purpose | Status |
|-------|---------|--------|
| users | User accounts (admin/teacher/student) | ✅ Used |
| subjects | Course definitions | ✅ Used |
| **class_assignments** | **Teacher→Subject→Cohort mapping** | **⚠️ MISSING DEFINITION** |
| student_details | Student cohort info (auto-created) | ✅ Used |
| evaluations | Rating/feedback records | ✅ Used |
| questions | Evaluation questions | ✅ Used |
| teacher_subjects | [UNUSED - Legacy] | ❌ Unused |
| student_subjects | [UNUSED - Legacy] | ❌ Unused |

---

## Query Logic by User Type

### Teacher Workflow

```
Login → Dashboard (shows subject/evaluation counts)
   ↓
My Subjects (lists courses taught)
   ├─ View Students (for each subject)
   └─ Evaluate Co-Teachers (other faculty to review)
```

**Key Query**:
```sql
FROM class_assignments ca
WHERE teacher_id = ?  -- Teacher's assignments
  AND ca.course = ? AND ca.year_level = ? AND ca.section = ?  -- Cohort
```

### Student Workflow

```
Login → Dashboard (shows pending evaluations)
   ↓
Evaluate Teachers (list pending subjects)
   └─ Submit evaluation for each teacher
```

**Key Query**:
```sql
FROM class_assignments ca
WHERE ca.course = ? AND ca.year_level = ? AND ca.section = ?  -- Student's cohort
  AND (no evaluation record exists)  -- Pending
```

---

## Critical Issue #1: Missing class_assignments Table

### Problem
The application uses `class_assignments` table extensively in every major query, but this table is **NOT** defined in the setup.sql file.

### Impact
- Database initialization will fail
- Cannot create teacher-subject-cohort relationships
- Entire system non-functional

### Location
- **Used in**: teacher/dashboard.php, teacher/my_subjects.php, teacher/view_students.php, student/evaluate.php, teacher/evaluate.php, admin/offerings.php
- **Missing from**: sql/setup.sql

### Solution
Add to sql/setup.sql:

```sql
CREATE TABLE IF NOT EXISTS class_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    course VARCHAR(10) NOT NULL,
    year_level INT NOT NULL,
    section VARCHAR(2) NOT NULL,
    semester VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id),
    UNIQUE KEY unique_assignment (teacher_id, subject_id, course, year_level, section, semester),
    INDEX idx_teacher (teacher_id),
    INDEX idx_subject (subject_id),
    INDEX idx_cohort (course, year_level, section)
);
```

---

## Critical Issue #2: Broken Student Count Query

### Problem
The "My Subjects" page shows 0 students for all classes.

### Root Cause
```php
LEFT JOIN student_subjects ss ON ss.subject_id = ca.subject_id
COALESCE(COUNT(DISTINCT ss.student_id), 0) as student_count
```

The `student_subjects` table is NEVER populated in the application, so count is always 0.

### Location
**File**: teacher/my_subjects.php (lines ~18-22)

### Solution
Change to use student_details (which IS populated):

```php
LEFT JOIN student_details sd 
    ON sd.course = ca.course 
    AND sd.year_level = ca.year_level 
    AND sd.section = ca.section
LEFT JOIN users u ON u.id = sd.user_id AND u.role = 'student'
COALESCE(COUNT(DISTINCT u.id), 0) as student_count
```

---

## Critical Issue #3: Student Data Inconsistency

### Problem
Student course/year/section info stored in TWO places:

1. **users table columns**: Used by student/evaluate.php
2. **student_details table**: Used by teacher/view_students.php and setup script

### Risk
If data not synchronized, queries return different results.

### Location
- **Write**: setup_student_details.php populates student_details
- **Read in student/evaluate.php**: Reads from users table
- **Read in teacher/view_students.php**: Reads from student_details table

### Solution
Consolidate to single source. **Recommendation**: Use `student_details` exclusively

```php
// Before (in student/evaluate.php)
SELECT course, year_level, section FROM users WHERE id = ?

// After
SELECT course, year_level, section FROM student_details WHERE user_id = ?
```

---

## Critical Issue #4: Unused Tables

### Problem
setup.sql creates tables that are never used:

```sql
CREATE TABLE teacher_subjects (...)  -- NOT USED
CREATE TABLE student_subjects (...)  -- NOT USED (but LEFT JOINed in broken query)
```

Meanwhile, the actual system uses `class_assignments` which isn't defined.

### Impact
- Code confusion/maintenance burden
- Dead code in database
- Unused foreign keys taking space

### Solution
Remove unused table definitions from setup.sql OR populate them consistently.

**Recommendation**: Remove them (simpler refactoring)

---

## Working Queries (Good Examples)

### ✅ View Students for Class
```sql
SELECT u.id, u.id_number, u.name, u.course, u.year_level, u.section
FROM student_details sd
RIGHT JOIN users u ON u.id = sd.user_id AND u.role = 'student'
WHERE sd.course = ? AND sd.year_level = ? AND sd.section = ?
ORDER BY u.name;
```

**Why it works**: Uses student_details which is populated, matches by cohort correctly

### ✅ Student Pending Evaluations
```sql
SELECT ca.id, s.subject_name, ca.subject_id, ca.teacher_id, u.name as teacher_name
FROM class_assignments ca
JOIN subjects s ON ca.subject_id = s.subject_id
JOIN users u ON ca.teacher_id = u.id
LEFT JOIN evaluations e ON e.student_id = ? AND e.teacher_id = ca.teacher_id AND e.subject_id = ca.subject_id
WHERE ca.course = ? AND ca.year_level = ? AND ca.section = ? AND e.id IS NULL
ORDER BY s.subject_name;
```

**Why it works**: Clear logic - find unevaluated subjects for this cohort

### ✅ Teacher Pending Evaluations
```sql
SELECT DISTINCT ca.subject_id, s.subject_name, u.id as teacher_id, u.name as teacher_name
FROM class_assignments ca
JOIN subjects s ON ca.subject_id = s.subject_id
JOIN users u ON ca.teacher_id = u.id
LEFT JOIN evaluations e ON e.teacher_id = u.id AND e.evaluator_role = 'teacher' AND e.evaluator_id = ? AND e.subject_id = ca.subject_id
WHERE ca.teacher_id != ? AND e.id IS NULL
ORDER BY s.subject_name, u.name;
```

**Why it works**: Finds all OTHER teachers' assignments, filters out already-evaluated

---

## Data Flow Diagram

```
SETUP PHASE:
    Admin creates Subjects
    Admin creates Users (Teachers, Students)
    Admin creates Class Assignments (teacher + subject + course/year/section)
    Setup script creates student_details from user course/year/section info

EVALUATION PHASE (Student):
    Student logs in
    Reads own: course, year_level, section
    Queries: class_assignments matching that cohort
    Shows: All subjects for that cohort not yet evaluated
    Evaluates: Each teacher
    Saves: INTO evaluations table (student_id, teacher_id, subject_id, rating, feedback)

EVALUATION PHASE (Teacher):
    Teacher logs in
    Queries: class_assignments where teacher_id = me
    Shows: My subjects (with broken student count)
    Can view: Students for each subject (queries student_details)
    Can evaluate: Other teachers' subjects (queries class_assignments where teacher_id != me)
    Saves: INTO evaluations table (evaluator_id, teacher_id, subject_id, rating, feedback)

ADMIN PHASE:
    Admin: Creates/edits/deletes class_assignments in admin/offerings.php
    Validation: Checks for duplicates before insert
```

---

## File Organization

```
BUSINESS LOGIC:
├─ teacher/dashboard.php ................ Dashboard with stats
├─ teacher/my_subjects.php ............. Subject list (ISSUE: student count broken)
├─ teacher/view_students.php ........... View students for subject (WORKS)
├─ student/evaluate.php ................ Evaluate teachers (WORKS)
├─ teacher/evaluate.php ................ Evaluate co-teachers (WORKS)

ADMINISTRATION:
├─ admin/offerings.php ................. Create/manage class_assignments
├─ admin/subjects.php .................. Subject management
├─ admin/manage_users.php .............. User management
├─ admin/setup_feedback_database.php ... Initialize questions table

DATABASE INITIALIZATION:
├─ sql/setup.sql ....................... Initial schema (INCOMPLETE)
├─ setup_student_details.php ........... Create student_details table (WORKS)
├─ validate_schema.php ................. Schema validator

CONFIGURATION:
├─ config/db.php ....................... PDO database connection
```

---

## Priority Fix List

### 🔴 CRITICAL (System won't work without this)
1. Add `class_assignments` table to setup.sql
2. Verify table exists in current database (if not, create manually)

### 🟠 HIGH (System works but broken features)
1. Fix student count query in teacher/my_subjects.php
2. Sync student data: Choose users OR student_details as single source
3. Ensure student_details is populated for all students

### 🟡 MEDIUM (Code quality)
1. Remove unused teacher_subjects/student_subjects tables
2. Add performance indexes (course/year/section lookups)
3. Add unit tests for queries

### 🔵 LOW (Nice to have)
1. Add audit logging for evaluations
2. Add data export/reporting
3. Performance monitoring

---

## Testing Checklist

```
Database Setup:
  [ ] class_assignments table exists
  [ ] student_details table exists  
  [ ] evaluations table has all columns
  [ ] questions table populated
  [ ] No errors in setup logs

Teacher Dashboard:
  [ ] Subject count displays correctly
  [ ] Evaluations done count shows correctly
  [ ] Pending reviews count accurate
  [ ] Semester info displays

My Subjects:
  [ ] Student count shows (not 0)
  [ ] Can click "View Students"
  
View Students:
  [ ] Shows all students in class cohort
  [ ] Students sortable by name
  [ ] Can navigate back

Student Evaluate:
  [ ] See only teachers for their classes
  [ ] See only unevaluated teachers
  [ ] Can submit evaluation
  [ ] Evaluation saved to database

Teacher Evaluate:
  [ ] See only other teachers
  [ ] See only unevaluated co-teachers
  [ ] Can submit evaluation
  [ ] Evaluation saved with evaluator_id

Admin Offerings:
  [ ] Can create new assignment
  [ ] Duplicate check prevents duplicates
  [ ] Can edit assignment
  [ ] Can delete assignment
```

---

## Performance Recommendations

### Recommended Indexes

```sql
-- Student matching (most common query type)
CREATE INDEX idx_student_details_cohort 
    ON student_details(course, year_level, section);

-- Evaluation lookups
CREATE INDEX idx_evaluations_student_teacher_subject 
    ON evaluations(student_id, teacher_id, subject_id);

CREATE INDEX idx_evaluations_evaluator 
    ON evaluations(evaluator_id, subject_id);

-- Class assignment lookups
CREATE INDEX idx_class_assignments_cohort 
    ON class_assignments(course, year_level, section);

CREATE INDEX idx_class_assignments_teacher 
    ON class_assignments(teacher_id);
```

### Query Optimization Tips

1. Avoid large LEFT JOINs - filter early with WHERE
2. Use DISTINCT only when necessary (adds overhead)
3. Index foreign key columns
4. Consider denormalizing cohort info if queries slow

---

## Quick Reference: Who Sees What

### Teacher Sees:
- ✅ Their assigned subjects (from class_assignments)
- ✅ Students in their classes (by course/year/section match)
- ✅ Evaluation questions (target_role='faculty')
- ✅ Other teachers (from class_assignments WHERE teacher_id != mine)
- ❌ Other teachers' students
- ❌ Student evaluations of themselves

### Student Sees:
- ✅ Their class subjects (by course/year/section match)
- ✅ Their teachers (from class_assignments for their cohort)
- ✅ Evaluation questions (target_role='student')
- ✅ Pending/completed status
- ❌ Other students
- ❌ Other cohorts' teachers

### Admin Sees:
- ✅ All users
- ✅ All subjects
- ✅ All class assignments
- ✅ Create/edit/delete any assignment

---

## Summary

The FEFS evaluation system uses a **cohort-based** model:
- Students are grouped by course/year/section
- Teachers are assigned to teach subjects TO specific cohorts
- Evaluations happen between members of the same cohort or across cohorts

**Main flow**: Student → User course/year/section → Find class_assignments matching cohort → See assigned teachers → Can evaluate them

**Current state**: Architecture sound but with 4 critical issues that must be fixed for functionality.

---

**Document**: EXECUTIVE_SUMMARY.md  
**Version**: 1.0  
**Date**: April 26, 2026  
**Status**: Ready for implementation
