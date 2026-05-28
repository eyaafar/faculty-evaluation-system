# 🔧 JotForm AI Integration - Configuration Required

## The Issue

Your JotForm AI responded: **"I need to see your actual evaluation scores..."**

This means JotForm AI hasn't been configured to access your feedback data yet.

---

## Why This Happens

We've **created and tested the API**, but JotForm's AI Agent doesn't know about it yet. You need to **configure it in the JotForm Dashboard**.

### What We've Built ✅
- ✅ Real-time API endpoint: `/teacher/api/system-data.php`
- ✅ API Key configured: `1b2423e7b7cba8c0d2105b08a7d57a49`
- ✅ Test data created: 6 evaluations for teacher 2
- ✅ Feedback page shows all data visibly
- ✅ Data is accessible and working

### What's Missing ⏳
- ⏳ **JotForm Dashboard Configuration** (You need to do this)

---

## How to Fix It - 2 Options

### Option 1: Quick Fix (Recommended) ⭐
**Make JotForm use the data visible on the page:**

The feedback page now displays all your metrics visibly:
- Overall rating (blue box at top)
- KPI cards (rating, evaluations, positive %)
- Rating distribution
- Question-by-question breakdown
- Help section with example questions

When you ask JotForm "Is the feedback doing well?", it should now be able to see this visible data on the page.

**Try this:** Reload the feedback page and ask JotForm again.

---

### Option 2: Proper Configuration (Best) 🎯
**Configure JotForm to call the API directly:**

1. **Go to JotForm Dashboard**
   - https://www.jotform.com/myforms

2. **Find Your Form**
   - Look for your FEFS feedback form or the form containing the AI Agent

3. **Access AI Settings**
   - Click "Edit" or "Settings"
   - Find **"AI Agent"**, **"Integrations"**, or **"Data Sources"** section

4. **Add API Data Source**
   - Click **"Add Data Source"** or **"Connect API"**
   - Fill in these details:

   **API URL:**
   ```
   http://localhost/FEFS/fe-system/teacher/api/system-data.php
   ```

   **Authentication:**
   ```
   api_key=1b2423e7b7cba8c0d2105b08a7d57a49&teacher_id=2&format=ai
   ```

   **Method:** GET  
   **Refresh Rate:** Real-time / On every query  
   **Cache:** Disabled

5. **Save and Test**
   - Click "Test Connection"
   - Should show "Success"
   - Ask JotForm: "What's my overall rating?"
   - Should respond with actual data (4/5, 6 evaluations, etc.)

---

## Quick Test

**Right now, ask JotForm:**
```
"Based on the feedback data shown on this page, is my teaching feedback doing well?"
```

If it can read the visible metrics, it will respond with specific numbers from your data.

---

## What the Data Shows

Your current feedback (visible on page):
- **Overall Rating:** 4/5 ⭐
- **Total Evaluations:** 6
- **Positive Feedback:** 70% (4-5 stars)
- **Negative Feedback:** 7% (1-2 stars)
- **Strong Areas:** Dismisses class on time (4.17/5), Comes prepared (4.17/5)
- **Areas to Improve:** Class attendance (3.83/5), Punctuality (3.83/5)

---

## API Endpoint Reference

**Live Endpoint (Always Fresh Data):**
```
http://localhost/FEFS/fe-system/teacher/api/system-data.php?api_key=1b2423e7b7cba8c0d2105b08a7d57a49&teacher_id=2&format=ai
```

**Test the API Directly:**
```
Paste this in browser: http://localhost/FEFS/fe-system/teacher/api/system-data.php?api_key=1b2423e7b7cba8c0d2105b08a7d57a49&teacher_id=2&format=json
```

---

## Troubleshooting

**JotForm still says "I need to see your data"?**
1. ✅ Check if data is visible on feedback page (blue box at top)
2. ✅ Reload the feedback page (Ctrl+Shift+R for hard refresh)
3. ✅ Try asking JotForm: "What rating did I get?" instead
4. ✅ Configure JotForm in dashboard (Option 2 above)

**API not responding?**
1. Check `/teacher/api/system-data.php` in browser
2. Verify teacher ID=2 has evaluations (6 on this system)
3. Check API key is correct: `1b2423e7b7cba8c0d2105b08a7d57a49`

**Want more test data?**
- Run: `http://localhost/FEFS/fe-system/create_test_evals_teacher2.php`
- Adds more evaluations for testing

---

## Next Steps

1. ✅ Try asking JotForm today (visible data)
2. ⏳ Configure JotForm dashboard this week (proper solution)
3. ⏳ Add more real evaluations as students submit them

**Status:** System is working. JotForm AI just needs configuration.
