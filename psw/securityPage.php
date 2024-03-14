<?php
    $csp = "Content-Security-Policy:";

    // No se pueden habilitar las google de apis porque tienen muchas subs apis.
    //$csp .= " default-src 'self'; ";
    $csp .= " script-src 'self' 'unsafe-eval' 'unsafe-inline'";
    $csp .=   " https://kit.fontawesome.com/a81368914c.js";

    $csp .= " child-src 'self' 'unsafe-eval' 'unsafe-inline'";

    $csp .= " frame-src 'self';";
    $csp .=   " https://fonts.googleapis.com";

    $csp .= " style-src 'self' 'unsafe-inline' https://openlayers.org;";

    header("X-Frame-Options: DENY");
    header("X-XSS-Protection: 1; mode=block");
    header( $csp );
?>