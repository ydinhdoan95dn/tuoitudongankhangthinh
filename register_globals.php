<?php
/**
 * Register Globals Function
 * Safe version - checks if arrays exist before iterating
 */
function register_globals() {
    // Nothing to do if register_globals is disabled
    if (ini_get('register_globals')) {
        return;
    }

    // Security: unset globals that could be exploited
    $superglobals = array(
        '_SESSION', '_COOKIE', '_POST', '_GET', '_REQUEST', '_SERVER', '_ENV', '_FILES'
    );

    foreach ($superglobals as $superglobal) {
        // Check if the superglobal exists and is an array
        if (isset($GLOBALS[$superglobal]) && is_array($GLOBALS[$superglobal])) {
            foreach ($GLOBALS[$superglobal] as $key => $value) {
                if (isset($GLOBALS[$key]) && !in_array($key, $superglobals)) {
                    unset($GLOBALS[$key]);
                }
            }
        }
    }
}
