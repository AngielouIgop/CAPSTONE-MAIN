/**
 * Inactivity Tracker - Auto-logout feature
 * Tracks user activity and automatically logs out after inactivity period
 */
(function() {
    'use strict';

    // Configuration
    const INACTIVITY_TIMEOUT = 1 * 60 * 1000; // 1 minute in milliseconds
    const HEARTBEAT_INTERVAL = 10 * 1000; // 10 seconds in milliseconds
    const CHECK_INTERVAL = 10 * 1000; // 10 seconds in milliseconds

    let lastActivityTime = Date.now();
    let heartbeatIntervalId = null;
    let checkIntervalId = null;
    let isLoggingOut = false;
    let mouseMoveThrottle = false;

    const activityEvents = [
        'mousedown',
        'mousemove',
        'keypress',
        'keydown',
        'scroll',
        'touchstart',
        'touchmove',
        'click',
        'wheel',
        'pointerdown',
        'pointermove'
    ];

    function updateActivity() {
        lastActivityTime = Date.now();
    }

    async function sendHeartbeat() {
        try {
            const response = await fetch('endpoints/activityHeartbeat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                console.warn('Heartbeat failed:', response.status);
                return;
            }

            const data = await response.json();
            
            if (data.logout === true || data.error === 'Session expired') {
                alert('Your session has expired. You will be redirected to the login page.');
                window.location.href = '?command=login';
            }
        } catch (error) {
            console.warn('Heartbeat error:', error);
        }
    }

    async function checkInactivity() {
        if (isLoggingOut) return;

        const timeSinceLastActivity = Date.now() - lastActivityTime;
        if (timeSinceLastActivity >= INACTIVITY_TIMEOUT) {
            await performAutoLogout();
        }
    }

    async function performAutoLogout() {
        if (isLoggingOut) return;

        isLoggingOut = true;

        try {
            const response = await fetch('endpoints/activityHeartbeat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=auto_logout',
                credentials: 'same-origin'
            });

            if (response.ok) {
                alert('Your session has expired due to inactivity. You will be redirected to the login page.');
                window.location.href = '?command=login';
            } else {
                window.location.href = '?command=logout';
            }
        } catch (error) {
            console.error('Auto-logout error:', error);
            window.location.href = '?command=logout';
        }
    }

    function init() {
        activityEvents.forEach(event => {
            if (event === 'mousemove' || event === 'pointermove') {
                document.addEventListener(event, function() {
                    if (!mouseMoveThrottle) {
                        mouseMoveThrottle = true;
                        updateActivity();
                        setTimeout(() => {
                            mouseMoveThrottle = false;
                        }, 1000);
                    }
                }, true);
            } else {
                document.addEventListener(event, updateActivity, true);
            }
        });

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                updateActivity();
                sendHeartbeat();
            }
        });

        window.addEventListener('focus', () => {
            updateActivity();
            sendHeartbeat();
        });

        window.addEventListener('blur', () => {
            updateActivity();
        });

        sendHeartbeat();
        heartbeatIntervalId = setInterval(sendHeartbeat, HEARTBEAT_INTERVAL);
        checkIntervalId = setInterval(checkInactivity, CHECK_INTERVAL);
        updateActivity();
        
        console.log('Inactivity tracker initialized - Auto-logout after 2 minutes of inactivity');
    }

    function cleanup() {
        if (heartbeatIntervalId) clearInterval(heartbeatIntervalId);
        if (checkIntervalId) clearInterval(checkIntervalId);
        activityEvents.forEach(event => {
            document.removeEventListener(event, updateActivity, true);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.addEventListener('beforeunload', cleanup);

    window.inactivityTracker = {
        updateActivity: updateActivity,
        performAutoLogout: performAutoLogout,
        cleanup: cleanup
    };
})();
