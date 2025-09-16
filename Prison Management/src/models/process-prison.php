<?php
include '../config/db_config.php';

// header('Content-Type: application/json');

$conn = getConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

$response = ['success' => false, 'message' => 'Unknown action'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $prisonId = $_POST['prisonId'] ?? '';
    $prisonName = $_POST['prisonName'] ?? '';
    $location = $_POST['location'] ?? '';
    $type = $_POST['type'] ?? '';
    $capacity = $_POST['capacity'] ?? 0;
    $numberOfCells = $_POST['numberOfCells'] ?? 0;
    $totalInmates = $_POST['totalInmates'] ?? 0;

    try {
        oci_set_client_info($conn, 'Prison Management System'); // Optional: Set client info for logging

        switch ($action) {
            case 'add':
                $sql = "INSERT INTO Prison (Prison_ID, Prison_Name, Location, Type, Capacity, NUMBER_OF_CELLS, TOTAL_INMATES) 
                        VALUES (prison_id_seq.NEXTVAL, :prison_name, :location, :type, :capacity, :number_of_cells, :total_inmates)";
                $stid = oci_parse($conn, $sql);
                oci_bind_by_name($stid, ':prison_name', $prisonName);
                oci_bind_by_name($stid, ':location', $location);
                oci_bind_by_name($stid, ':type', $type);
                oci_bind_by_name($stid, ':capacity', $capacity);
                oci_bind_by_name($stid, ':number_of_cells', $numberOfCells);
                oci_bind_by_name($stid, ':total_inmates', $totalInmates);
                $result = oci_execute($stid);
                $response = ['success' => $result, 'message' => $result ? 'Prison added successfully.' : 'Failed to add prison.'];
                break;

            case 'update':
                if ($prisonId) {
                    $sql = "UPDATE Prison SET Prison_Name = :prison_name, Location = :location, Type = :type, 
                            Capacity = :capacity, NUMBER_OF_CELLS = :number_of_cells, TOTAL_INMATES = :total_inmates 
                            WHERE Prison_ID = :prison_id";
                    $stid = oci_parse($conn, $sql);
                    oci_bind_by_name($stid, ':prison_id', $prisonId);
                    oci_bind_by_name($stid, ':prison_name', $prisonName);
                    oci_bind_by_name($stid, ':location', $location);
                    oci_bind_by_name($stid, ':type', $type);
                    oci_bind_by_name($stid, ':capacity', $capacity);
                    oci_bind_by_name($stid, ':number_of_cells', $numberOfCells);
                    oci_bind_by_name($stid, ':total_inmates', $totalInmates);
                    $result = oci_execute($stid);
                    $response = ['success' => $result, 'message' => $result ? 'Prison updated successfully.' : 'Failed to update prison.'];
                } else {
                    $response = ['success' => false, 'message' => 'Please select a prison to update.'];
                }
                break;

            case 'delete':
                if ($prisonId) {
                    $success = true;
                    $message = "Prison ID $prisonId and all related records deleted successfully.";
                    
                    $stmts = [
                        "DELETE FROM Prisoner_Lawyer WHERE Prisoner_ID IN (SELECT Prisoner_ID FROM Prisoner WHERE Prison_ID = :prison_id)",
                        "DELETE FROM Prisoner_MedicalWorker WHERE Prisoner_ID IN (SELECT Prisoner_ID FROM Prisoner WHERE Prison_ID = :prison_id)",
                        "DELETE FROM Prisoner_Visitor WHERE Prisoner_ID IN (SELECT Prisoner_ID FROM Prisoner WHERE Prison_ID = :prison_id)",
                        "DELETE FROM Prisoner_Staff WHERE Prisoner_ID IN (SELECT Prisoner_ID FROM Prisoner WHERE Prison_ID = :prison_id)",
                        "DELETE FROM Prisoner_Case WHERE Prisoner_ID IN (SELECT Prisoner_ID FROM Prisoner WHERE Prison_ID = :prison_id)",
                        "DELETE FROM Prisoner WHERE Prison_ID = :prison_id",
                        "DELETE FROM Prisoner_Staff WHERE Staff_ID IN (SELECT Staff_ID FROM Staff WHERE Prison_ID = :prison_id)",
                        "DELETE FROM Staff WHERE Prison_ID = :prison_id",
                        "DELETE FROM Warden WHERE Prison_ID = :prison_id",
                        "DELETE FROM Cell WHERE Prison_ID = :prison_id",
                        "DELETE FROM Prison WHERE Prison_ID = :prison_id"
                    ];

                    foreach ($stmts as $sql) {
                        $stid = oci_parse($conn, $sql);
                        oci_bind_by_name($stid, ':prison_id', $prisonId);
                        $result = oci_execute($stid);
                        if (!$result) {
                            $error = oci_error($stid);
                            $success = false;
                            $message = 'Error: ' . ($error['message'] ?? 'Unknown error during deletion');
                            break;
                        }
                    }

                    // Commit or rollback based on success
                    if ($success) {
                        oci_commit($conn);
                    } else {
                        oci_rollback($conn);
                    }

                    $response = ['success' => $success, 'message' => $message];
                } else {
                    $response = ['success' => false, 'message' => 'Please select a prison to delete.'];
                }
                break;
        }
    } catch (Exception $e) {
        oci_rollback($conn); // Rollback on PHP exception
        $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }

    oci_close($conn);
    // echo json_encode($response);
    echo "<script>
        alert('" . addslashes($response['message']) . "');
        window.location.href = '../php/prison-manage.php';
    </script>";
    exit;
}