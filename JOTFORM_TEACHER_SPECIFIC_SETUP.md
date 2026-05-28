# 🔧 JotForm AI - Teacher-Specific Data Configuration

## The Problem
When you tested the session-based URL (`?format=ai`), it returned "Unauthorized" because:
- Session cookies don't work across ngrok tunnels
- JotForm AI needs to identify which teacher is asking the question
- The hardcoded `teacher_id=2` shows Mr. Juan Dela Cruz's data to ALL teachers

## The Solution

### ✅ Option 1: Teacher-Specific API Endpoint (Recommended)

**Use the new endpoint I created:**
```
https://yeah-treadmill-tingling.ngrok-free.dev/FEFS/fe-system/teacher/api/teacher-specific-data.php
```

**For JotForm AI Configuration:**
1. **Go to JotForm Dashboard** → Your AI Agent Settings
2. **Update the API URL to:**
   ```
   https://yeah-treadmill-tingling.ngrok-free.dev/FEFS/fe-system/teacher/api/teacher-specific-data.php?api_key=1b2423e7b7cba8c0d2105b08a7d57a49&teacher_id={{teacher_id}}&format=ai
   ```
3. **Configure JotForm to get teacher_id dynamically:**
   - Add JavaScript to your page that gets the teacher ID from the session
   - Or use a placeholder that gets replaced with the actual teacher ID

### ✅ Option 2: Dynamic Teacher ID from Page Context

**Add this JavaScript to your feedback.php page:**
```javascript
// Get teacher ID from PHP session
window.FEFS_CURRENT_TEACHER = {
    id: <?php echo (int)$teacher_id; ?>,
    name: <?php echo json_encode($_SESSION['name'] ?? 'Teacher'); ?>
};

// Function to get the correct API URL
function getTeacherAPIUrl() {
    const teacherId = window.FEFS_CURRENT_TEACHER.id;
    return `https://yeah-treadmill-tingling.ngrok-free.dev/FEFS/fe-system/teacher/api/system-data.php?api_key=1b2423e7b7cba8c0d2105b08a7d57a49&teacher_id=${teacherId}&format=ai`;
}
```

**Then configure JotForm to call:**
```javascript
// In JotForm AI configuration
const apiUrl = getTeacherAPIUrl();
```

### ✅ Option 3: Session-Based with Proxy (Most Secure)

**Create a simple proxy endpoint:**
```php
<?php
// teacher-proxy.php - Add this to your teacher/api folder
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    http_response_code(403);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$teacher_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0;
$api_key = '1b2423e7b7cba8c0d2105b08a7d57a49';

// Call the main API with current teacher's ID
$url = "http://localhost/FEFS/fe-system/teacher/api/system-data.php?api_key={$api_key}&teacher_id={$teacher_id}&format=ai";

echo file_get_contents($url);
?>
```

**Then use this URL in JotForm:**
```
https://yeah-treadmill-tingling.ngrok-free.dev/FEFS/fe-system/teacher/api/teacher-proxy.php?format=ai
```

## 🧪 Testing the Fix

**Test with different teachers:**

1. **Log in as Dr. Pedro Reyes**
2. **Go to:** `http://localhost/FEFS/fe-system/test-teacher-specific-data.php`
3. **Verify you see Dr. Reyes' data**
4. **Log out and log in as Mr. Juan Dela Cruz**
5. **Refresh the test page**
6. **Verify you see Mr. Dela Cruz's data**

## 📋 JotForm AI Agent Instructions Update

**Update your Agent Prompt to include:**
```
When a teacher asks about their feedback, ALWAYS use the Send API Request tool with their specific teacher ID.

API URL Format: https://yeah-treadmill-tingling.ngrok-free.dev/FEFS/fe-system/teacher/api/teacher-specific-data.php?api_key=1b2423e7b7cba8c0d2105b08a7d57a49&teacher_id={{teacher_id}}&format=ai

The teacher ID should be obtained from the page context (window.FEFS_CURRENT_TEACHER.id).
```

## ✅ Expected Results

**Before Fix:**
- Dr. Reyes asks: "What's my rating?" → Gets Mr. Dela Cruz's rating (4.2/5)
- Mr. Dela Cruz asks: "What's my rating?" → Gets his own rating (4.2/5)
- **Problem:** Cross-contamination!

**After Fix:**
- Dr. Reyes asks: "What's my rating?" → Gets **his actual rating**
- Mr. Dela Cruz asks: "What's my rating?" → Gets **his actual rating**
- **Solution:** Each teacher sees only their own data!

## 🔍 Verification

**Test URLs:**
- **Dr. Pedro Reyes:** `https://yeah-treadmill-tingling.ngrok-free.dev/FEFS/fe-system/teacher/api/teacher-specific-data.php?api_key=1b2423e7b7cba8c0d2105b08a7d57a49&teacher_id=3&format=ai`
- **Mr. Juan Dela Cruz:** `https://yeah-treadmill-tingling.ngrok-free.dev/FEFS/fe-system/teacher/api/teacher-specific-data.php?api_key=1b2423e7b7cba8c0d2105b08a7d57a49&teacher_id=2&format=ai`

**Each should return different data specific to that teacher!** 🎉