<?php
/**
 * JOTFORM AI AGENT KNOWLEDGE BASE SETUP GUIDE
 * Shows exactly where to paste the API configuration
 */

echo "=== JOTFORM AI AGENT KNOWLEDGE BASE SETUP ===\n\n";

echo "📍 WHERE TO PASTE THE API CONFIGURATION:\n\n";

echo "1. LOGIN TO JOTFORM:\n";
echo "   - Go to https://www.jotform.com\n";
echo "   - Sign in to your account\n\n";

echo "2. ACCESS AI AGENT SETTINGS:\n";
echo "   - Navigate to Settings → AI Agent\n";
echo "   - OR look for 'AI Assistant' in your form settings\n\n";

echo "3. FIND KNOWLEDGE BASE SECTION:\n";
echo "   - Look for 'Knowledge Base', 'Training Data', or 'Context'\n";
echo "   - This is usually a text area where you can input information\n\n";

echo "4. PASTE THIS EXACT TEXT:\n";
echo "   ========================================\n";
echo "   API Configuration:\n\n";
echo "   Base URL: https://yeah-treadmill-tingling.ngrok-free.dev/FEFS/fe-system/teacher/api/recommendations.php\n";
echo "   API Key: 1b2423e7b7cba8c0d2105b08a7d57a49\n";
echo "   Parameters: teacher_id={teacher_id}&format=ai\n";
echo "   Response Format: JSON with teacher evaluation data and AI-optimized recommendations\n\n";
echo "   Teacher ID Mapping:\n";
echo "   - Teacher 2: Juan Dela Cruz\n";
echo "   - Teacher 3: Maria Santos\n";
echo "   - Teacher 4: Dr. Pedro Reyes\n\n";
echo "   Example API Call:\n";
echo "   https://yeah-treadmill-tingling.ngrok-free.dev/FEFS/fe-system/teacher/api/recommendations.php?api_key=1b2423e7b7cba8c0d2105b08a7d57a49&teacher_id=4&format=ai\n";
echo "   ========================================\n\n";

echo "5. SAVE SETTINGS:\n";
echo "   - Click 'Save' or 'Update' to apply changes\n\n";

echo "6. TEST THE INTEGRATION:\n";
echo "   - Create a test form\n";
echo "   - Ask Professor Jag about teacher recommendations\n";
echo "   - He should now be able to access real teacher data!\n\n";

echo "🔍 ALTERNATIVE LOCATIONS (if you can't find Knowledge Base):\n\n";
echo "- Look for 'Custom Instructions' or 'System Prompt'\n";
echo "- Check under 'Advanced Settings' → 'AI Configuration'\n";
echo "- Look for 'Context Window' or 'Background Information'\n";
echo "- Check 'Training Data' or 'Reference Materials'\n\n";

echo "⚠️  IMPORTANT NOTES:\n";
echo "- Make sure ngrok tunnel is running on your local machine\n";
echo "- The URL must be accessible from JotForm's servers\n";
echo "- Test the URL in your browser first before pasting\n";
echo "- Professor Jag should now say 'Based on your evaluation data...' instead of 'I cannot query SQL dumps'\n\n";

echo "=== SETUP COMPLETE! ===\n";
echo "Professor Jag can now access real teacher evaluation data!\n";
?>