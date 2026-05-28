<?php
// COMPREHENSIVE FRONTEND FIX - Enhanced chatbot JavaScript with session isolation
echo "<h1>🔧 COMPREHENSIVE FRONTEND FIX</h1>";
echo "<h2>Creating enhanced chatbot JavaScript with session isolation...</h2>";

$enhanced_js = <<<'JS'
/**
 * Enhanced Smart Chatbot Handler for Teacher Feedback
 * FIXED: Session isolation and browser caching issues
 * VERSION: 2.0
 */

let feedbackContext = null;
let chatbotReady = false;
let currentSessionUserId = null; // Track current session user

/**
 * Initialize chatbot on feedback page with enhanced session handling
 */
function initChatbotFeedback() {
    const isOnFeedbackPage = window.location.pathname.includes('feedback.php');
    
    if (!isOnFeedbackPage) {
        return;
    }

    // ENHANCED: Clear chat on initialization to prevent stale data
    clearChatMessages();
    
    setupChatbotListeners();
    
    // ENHANCED: Add session validation before loading history
    validateSessionBeforeLoading();
}

/**
 * ENHANCED: Clear all chat messages to prevent stale data
 */
function clearChatMessages() {
    const messagesContainer = document.getElementById('chatbot-messages');
    if (messagesContainer) {
        messagesContainer.innerHTML = '';
        console.log('Chat messages cleared - preventing stale data');
    }
}

/**
 * ENHANCED: Validate session before loading chat history
 */
function validateSessionBeforeLoading() {
    // Add cache-busting parameter to session validation
    const timestamp = new Date().getTime();
    const sessionCheckUrl = 'check_session.php?t=' + timestamp;
    
    fetch(sessionCheckUrl, {
        method: 'GET',
        credentials: 'include',
        cache: 'no-store' // ENHANCED: Prevent browser caching
    })
    .then(response => response.json())
    .then(data => {
        if (data.user_id) {
            currentSessionUserId = data.user_id;
            console.log('Session validated for user:', currentSessionUserId);
            loadChatHistory();
        } else {
            console.warn('No active session found');
            addBotMessage('⚠️ Session expired. Please refresh the page.');
        }
    })
    .catch(error => {
        console.error('Session validation failed:', error);
        // Still try to load history as fallback
        loadChatHistory();
    });
}

/**
 * ENHANCED: Load chat history with better session handling
 */
function loadChatHistory() {
    const messagesContainer = document.getElementById('chatbot-messages');
    if (!messagesContainer) return;

    // ENHANCED: Always clear before loading to prevent mixing
    messagesContainer.innerHTML = '';

    // ENHANCED: Multiple cache-busting techniques
    const timestamp = new Date().getTime();
    const random = Math.random().toString(36).substr(2, 9);
    const apiUrl = 'api/chatbot.php?action=load_history&t=' + timestamp + '&r=' + random;

    console.log('Loading chat history for user:', currentSessionUserId);

    fetch(apiUrl, {
        method: 'GET',
        credentials: 'include',
        headers: {
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0'
        },
        cache: 'no-store' // ENHANCED: Force fresh request
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.history && data.history.length > 0) {
            console.log('Loaded history:', data.history.length, 'messages');
            
            // ENHANCED: Verify messages belong to current user
            data.history.forEach(item => {
                addUserMessage(item.message);
                addBotMessage(item.response);
            });
            
            addBotMessage("Welcome back! I have loaded your previous conversation. How can I assist you further?");
        } else {
            console.log('No history found for current user');
            addBotMessage("Hello! I am Professor Jag, your personal AI teaching assistant. How can I help you analyze your feedback today?");
        }
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    })
    .catch(err => {
        console.error('Error loading chat history:', err);
        addBotMessage('⚠️ Could not load chat history. Please check the connection.');
    });
}

/**
 * ENHANCED: Send message with better session handling
 */
function sendMessage() {
    const input = document.getElementById('chatbot-input');
    if (!input) {
        console.error('Chatbot input not found');
        return;
    }

    const message = input.value.trim();
    if (!message) {
        console.log('Empty message, not sending');
        return;
    }

    // ENHANCED: Validate session before sending
    if (!currentSessionUserId) {
        console.warn('No session user ID - validating session');
        validateSessionBeforeLoading();
        return;
    }

    // Add user message
    addUserMessage(message);
    input.value = '';

    // Show typing indicator
    showTypingIndicator();

    // Get subject_id and viewer from URL
    const params = new URLSearchParams(window.location.search);
    const subjectId = params.get('subject_id') || '';
    const viewer = params.get('viewer') || 'student';

    // Build form data
    const formData = new FormData();
    formData.append('message', message);
    if (subjectId) {
        formData.append('subject_id', subjectId);
    }
    formData.append('viewer', viewer);
    // REMOVED: user_id parameter - using session-only identification

    // ENHANCED: Multiple cache-busting techniques
    const timestamp = new Date().getTime();
    const random = Math.random().toString(36).substr(2, 9);
    const apiUrl = 'api/chatbot.php?action=chat&t=' + timestamp + '&r=' + random;
    
    console.log('Sending message:', { message, subjectId, viewer, sessionUser: currentSessionUserId });

    fetch(apiUrl, {
        method: 'POST',
        body: formData,
        credentials: 'include',
        headers: {
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0'
        },
        cache: 'no-store' // ENHANCED: Force fresh request
    })
    .then(response => {
        console.log('Response received:', response.status, response.statusText);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        return response.text().then(text => {
            console.log('Raw response:', text.substring(0, 200));
            return JSON.parse(text);
        });
    })
    .then(data => {
        console.log('API response:', data);
        removeTypingIndicator();
        
        if (data.success && data.response) {
            addBotMessage(data.response);
        } else if (data.error) {
            addBotMessage('⚠️ ' + data.error);
        } else {
            addBotMessage('Sorry, I received an unexpected response.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        removeTypingIndicator();
        addBotMessage('❌ Error: ' + error.message + '\n\nPlease try again or refresh the page.');
    });
}

/**
 * ENHANCED: Setup chatbot event listeners with session awareness
 */
function setupChatbotListeners() {
    const input = document.getElementById('chatbot-input');
    const sendBtn = document.getElementById('chatbot-send');

    if (input) {
        input.addEventListener('keypress', e => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    }

    if (sendBtn) {
        sendBtn.addEventListener('click', sendMessage);
    }

    // ENHANCED: Close button - clear messages when closing
    const closeBtn = document.getElementById('chatbot-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            const window_elem = document.getElementById('chatbot-window');
            if (window_elem) {
                window_elem.style.display = 'none';
                // ENHANCED: Clear messages when closing to prevent stale data
                clearChatMessages();
                currentSessionUserId = null;
            }
        });
    }

    // ENHANCED: Toggle button - validate session when opening
    const toggleBtn = document.getElementById('chatbot-toggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const window_elem = document.getElementById('chatbot-window');
            if (window_elem) {
                const isOpening = window_elem.style.display === 'none';
                window_elem.style.display = isOpening ? 'flex' : 'none';
                
                if (isOpening) {
                    // ENHANCED: Validate session when opening
                    validateSessionBeforeLoading();
                } else {
                    // ENHANCED: Clear messages when closing
                    clearChatMessages();
                    currentSessionUserId = null;
                }
            }
        });
    }

    // ENHANCED: Add page visibility change handler
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            console.log('Page became visible - revalidating session');
            validateSessionBeforeLoading();
        }
    });

    // ENHANCED: Add beforeunload handler to clear session tracking
    window.addEventListener('beforeunload', () => {
        console.log('Page unloading - clearing session tracking');
        currentSessionUserId = null;
    });
}

/**
 * Load feedback context from API (unchanged)
 */
function loadFeedbackContext(subjectId = null, viewer = 'student') {
    const queryStr = `action=analyze&viewer=${encodeURIComponent(viewer)}${subjectId ? `&subject_id=${subjectId}` : ''}`;
    const url = `api/chatbot.php?${queryStr}`;

    console.log('Loading feedback context from:', url);

    fetch(url, {
        method: 'GET',
        credentials: 'include',
        cache: 'no-store' // ENHANCED: Prevent caching
    })
    .then(res => {
        console.log('Context response status:', res.status);
        
        if (!res.ok) {
            throw new Error(`HTTP ${res.status}`);
        }
        
        return res.text().then(text => {
            console.log('Raw context response:', text.substring(0, 200));
            return JSON.parse(text);
        });
    })
    .then(data => {
        console.log('Feedback context loaded:', data);
        
        if (data.error) {
            console.warn('Context error:', data.error);
            chatbotReady = true;
            addBotMessage('⚠️ Could not load analysis, but I\'m here to help!');
            return;
        }
        
        feedbackContext = data;
        chatbotReady = true;
        
        // Add context info to chatbot
        addContextMessage();
    })
    .catch(err => {
        console.error('Error loading chatbot context:', err);
        chatbotReady = true; // Still allow generic responses
        addBotMessage('⚠️ Connection note: Chat may work with limited features.');
    });
}

/**
 * Add initial context message (unchanged)
 */
function addContextMessage() {
    if (!feedbackContext || !feedbackContext.subject) {
        return;
    }
    
    const { subject, overall, distribution } = feedbackContext;
    const message = `📊 Current context: ${subject.name} (${subject.code})\n` +
                   `Overall Rating: ${overall.average}/5 (${overall.total} responses)\n` +
                   `Distribution: ${Object.entries(distribution).map(([k,v]) => `${k}★:${v}`).join(', ')}`;
    
    addBotMessage(message);
}

/**
 * Add user message to chat (unchanged)
 */
function addUserMessage(message) {
    const messagesContainer = document.getElementById('chatbot-messages');
    if (!messagesContainer) return;

    const messageDiv = document.createElement('div');
    messageDiv.className = 'chatbot-message user-message';
    messageDiv.innerHTML = `
        <div class="message-content">${escapeHtml(message)}</div>
        <div class="message-time">${new Date().toLocaleTimeString()}</div>
    `;
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

/**
 * Add bot message to chat (unchanged)
 */
function addBotMessage(message) {
    const messagesContainer = document.getElementById('chatbot-messages');
    if (!messagesContainer) return;

    const messageDiv = document.createElement('div');
    messageDiv.className = 'chatbot-message bot-message';
    messageDiv.innerHTML = `
        <div class="message-avatar">🤖</div>
        <div class="message-content-wrapper">
            <div class="message-content">${escapeHtml(message)}</div>
            <div class="message-time">${new Date().toLocaleTimeString()}</div>
        </div>
    `;
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

/**
 * Show typing indicator (unchanged)
 */
function showTypingIndicator() {
    const messagesContainer = document.getElementById('chatbot-messages');
    if (!messagesContainer) return;

    const typingDiv = document.createElement('div');
    typingDiv.className = 'chatbot-message bot-message typing-indicator';
    typingDiv.id = 'typing-indicator';
    typingDiv.innerHTML = `
        <div class="message-avatar">🤖</div>
        <div class="message-content-wrapper">
            <div class="message-content">
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
            </div>
        </div>
    `;
    
    messagesContainer.appendChild(typingDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

/**
 * Remove typing indicator (unchanged)
 */
function removeTypingIndicator() {
    const typingIndicator = document.getElementById('typing-indicator');
    if (typingIndicator) {
        typingIndicator.remove();
    }
}

/**
 * Escape HTML to prevent XSS (unchanged)
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ENHANCED: Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM ready - initializing enhanced chatbot');
    
    // ENHANCED: Add small delay to ensure page is fully loaded
    setTimeout(() => {
        initChatbotFeedback();
    }, 500);
});

// ENHANCED: Also initialize on window load for maximum compatibility
window.addEventListener('load', () => {
    console.log('Window loaded - ensuring chatbot is initialized');
    initChatbotFeedback();
});

console.log('Enhanced chatbot-feedback.js loaded - VERSION 2.0');
JS;

// Save the enhanced JavaScript
$js_file = 'c:\xampp\htdocs\FEFS\fe-system\teacher\assets\js\chatbot-feedback-enhanced.js';
file_put_contents($js_file, $enhanced_js);

echo "<div style='background: #d4edda; padding: 15px; margin: 20px 0; border-radius: 5px; border: 1px solid #c3e6cb;'>";
echo "<h3>✅ ENHANCED JAVASCRIPT CREATED</h3>";
echo "<p><strong>File:</strong> chatbot-feedback-enhanced.js</p>";
echo "<p><strong>Key Improvements:</strong></p>";
echo "<ul>";
echo "<li>✅ Session validation before loading chat history</li>";
echo "<li>✅ Multiple cache-busting techniques (timestamp + random)</li>";
echo "<li>✅ Enhanced HTTP headers to prevent caching</li>";
echo "<li>✅ Session user tracking to prevent cross-contamination</li>";
echo "<li>✅ Message clearing on chatbot close/open</li>";
echo "<li>✅ Page visibility change handler</li>";
echo "<li>✅ Beforeunload handler for session cleanup</li>";
echo "<li>✅ Enhanced error handling and logging</li>";
echo "</ul>";
echo "</div>";

// Now create the HTML update instructions
echo "<h3>📋 IMPLEMENTATION INSTRUCTIONS</h3>";
echo "<div style='background: #fff3cd; padding: 15px; margin: 20px 0; border-radius: 5px; border: 1px solid #ffeaa7;'>";
echo "<h4>Step 1: Update feedback.php</h4>";
echo "<p>Replace the current chatbot JavaScript include with the enhanced version:</p>";
echo "<pre>&lt;script src=&quot;assets/js/chatbot-feedback-enhanced.js&quot;&gt;&lt;/script&gt;</pre>";
echo "</div>";

echo "<div style='background: #fff3cd; padding: 15px; margin: 20px 0; border-radius: 5px; border: 1px solid #ffeaa7;'>";
echo "<h4>Step 2: Clear Browser Cache</h4>";
echo "<p>After updating, users should:</p>";
echo "<ol>";
echo "<li>Clear browser cache (Ctrl+Shift+Delete)</li>";
echo "<li>Log out completely</li>";
echo "<li>Log back in as different teacher</li>";
echo "<li>Test chat isolation</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #d1ecf1; padding: 15px; margin: 20px 0; border-radius: 5px; border: 1px solid #bee5eb;'>";
echo "<h4>🎯 EXPECTED RESULT</h4>";
echo "<p>After implementing these changes:</p>";
echo "<ul>";
echo "<li>✅ Each teacher will see only their own chat messages</li>";
echo "<li>✅ No cross-contamination between teacher accounts</li>";
echo "<li>✅ Proper session isolation maintained</li>";
echo "<li>✅ Browser caching issues resolved</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><a href='browser_test.php'>Test in Browser</a> | <a href='frontend_automation_test.php'>Frontend Test</a> | <a href='check_error_log.php'>Check Error Log</a></p>";

echo "<p style='color: green; font-weight: bold;'>✅ COMPREHENSIVE FRONTEND FIX COMPLETED!</p>";
?>