<?php
/**
 * Debug helper functions for troubleshooting the auto-refresh issue
 */

/**
 * Captures and logs request information for debugging
 */
function captureRequestDebugInfo() {
    // Get request information
    $requestInfo = [
        'time' => date('Y-m-d H:i:s'),
        'method' => $_SERVER['REQUEST_METHOD'],
        'uri' => $_SERVER['REQUEST_URI'],
        'referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'none',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'get_params' => $_GET,
        'post_params' => $_POST,
        'headers' => getRequestHeaders(),
        'session' => isset($_SESSION) ? session_id() : 'none'
    ];
    
    // Log to file
    error_log("REQUEST DEBUG: " . json_encode($requestInfo, JSON_PRETTY_PRINT));
    
    // Also write to a dedicated debug log file
    file_put_contents(__DIR__ . '/debug.log', 
        date('[Y-m-d H:i:s]') . ' REQUEST: ' . json_encode($requestInfo, JSON_PRETTY_PRINT) . "\n", 
        FILE_APPEND
    );
}

/**
 * Get all request headers
 * 
 * @return array All request headers
 */
function getRequestHeaders() {
    if (function_exists('getallheaders')) {
        return getallheaders();
    }
    
    // Fallback implementation if getallheaders() is not available
    $headers = [];
    foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
    }
    return $headers;
}

/**
 * Adds a JavaScript snippet to detect and log page refresh events
 */
function addRefreshDetectionJs() {
    ?>
    <script>
    // Track page loads
    window.addEventListener('load', function() {
        console.log('Page loaded at: ' + new Date().toISOString());
        
        // Send AJAX request to log this page load
        const data = new FormData();
        data.append('event', 'page_load');
        data.append('timestamp', new Date().toISOString());
        data.append('navigation_type', getNavigationType());
        data.append('url', window.location.href);
        
        fetch('debug_log.php', {
            method: 'POST',
            body: data
        }).catch(error => console.error('Debug logging error:', error));
    });
    
    // Track when the page is about to be unloaded
    window.addEventListener('beforeunload', function(event) {
        console.log('Page unloading at: ' + new Date().toISOString());
        
        // Send synchronous request to log the unload event
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'debug_log.php', false); // false makes it synchronous
        const data = new FormData();
        data.append('event', 'page_unload');
        data.append('timestamp', new Date().toISOString());
        data.append('url', window.location.href);
        xhr.send(data);
    });
    
    // Detect navigation type (reload, navigation, etc.)
    function getNavigationType() {
        const performance = window.performance;
        
        if (!performance) {
            return "Navigation type not supported";
        }
        
        const navigation = performance.navigation;
        
        if (!navigation) {
            return "Navigation details not supported";
        }
        
        switch(navigation.type) {
            case 0: return "Navigation";
            case 1: return "Reload";
            case 2: return "Back/Forward";
            default: return "Unknown";
        }
    }
    </script>
    <?php
}
