<?php
    $db_host = getenv('DB_HOST');
    $db_user = getenv('DB_USER');
    $db_pass = getenv('DB_PASS');
    $db_name = getenv('DB_NAME');

    // Path to the system's CA certificate bundle
    // This file is provided by the 'ca-certificates' package in your Docker container
    $ca_path = '/etc/ssl/certs/ca-certificates.crt';

    // Initialize mysqli
    $con = mysqli_init();
    if (!$con) {
        die("mysqli_init failed");
    }

    // Set SSL options
    // We provide the path to the CA bundle to verify the server's certificate
    mysqli_ssl_set($con, NULL, NULL, $ca_path, NULL, NULL);

    // Tell mysqli to verify the server's certificate
    // This is required by TiDB
    mysqli_options($con, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);

    // Establish the secure connection
    $connected = mysqli_real_connect(
        $con,
        $db_host,
        $db_user,
        $db_pass,
        $db_name,
        4000, // TiDB Cloud port
        NULL,
        MYSQLI_CLIENT_SSL // Force SSL
    );

    if (!$connected) {
        // This will now give a more specific SSL error if it fails
        die("Connection failed: (" . mysqli_connect_errno() . ") " . mysqli_connect_error());
    }
?>