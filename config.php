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

    /**
     * Helper: execute a SQL query with PDO-style named parameters using mysqli.
     * - Replaces :name placeholders with ? in order of appearance
     * - Binds all parameters as strings ("s") by default
     * - Returns the mysqli_stmt on success or false on failure
     *
     * Usage:
     *   $stmt = execute_named_query($con, $sql, [':id' => $id, ':name' => $name]);
     */
    function execute_named_query($con, $sql, $params = []) {
        // Find placeholders in order
        preg_match_all('/(:[a-zA-Z_][a-zA-Z0-9_]*)/', $sql, $matches);
        $placeholders = $matches[1];

        if (!empty($placeholders)) {
            // Replace named placeholders with ? one-by-one
            foreach ($placeholders as $ph) {
                $sql = preg_replace('/' . preg_quote($ph, '/') . '/', '?', $sql, 1);
            }
        }

        $stmt = $con->prepare($sql);
        if ($stmt === false) return false;

        if (!empty($placeholders) && !empty($params)) {
            // Build ordered params array based on placeholders order
            $ordered = [];
            foreach ($placeholders as $ph) {
                // allow keys with or without leading colon
                if (array_key_exists($ph, $params)) {
                    $ordered[] = $params[$ph];
                } else {
                    $key = ltrim($ph, ':');
                    if (array_key_exists($key, $params)) {
                        $ordered[] = $params[$key];
                    } else {
                        // missing param - bind null
                        $ordered[] = null;
                    }
                }
            }

            // Prepare type string (all strings by default)
            $types = str_repeat('s', count($ordered));

            // bind_param requires references
            $bind_names[] = $types;
            for ($i = 0; $i < count($ordered); $i++) {
                $bind_name = 'bind' . $i;
                $$bind_name = $ordered[$i];
                $bind_names[] = &$$bind_name;
            }

            call_user_func_array([$stmt, 'bind_param'], $bind_names);
        }

        if (!$stmt->execute()) {
            return false;
        }

        return $stmt;
    }
?>