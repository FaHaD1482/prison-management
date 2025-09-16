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
    $staffId = $_POST['staffId'] ?? '';
    $staffName = $_POST['staffName'] ?? '';
    $role = $_POST['role'] ?? '';
    $shiftTiming = $_POST['shiftTiming'] ?? '';
    $specialization = $_POST['specialization'] ?? '';
    $phone1 = $_POST['phone1'] ?? '';
    $phone2 = $_POST['phone2'] ?? '';
    $assignedId = $_POST['assignedId'] ?? '';

    try {
        oci_execute(oci_parse($conn, 'BEGIN DBMS_OUTPUT.ENABLE; END;')); // Enable output for debugging if needed

        switch ($action) {
            case 'add':
                $sql = "INSERT INTO Staff (Staff_ID, Ch_Name, Ch_Role, CH_Shift_Timings, Ch_Specialization, Prison_ID, Ch_Phone1, Ch_Phone2) 
                        VALUES (staff_id_seq.NEXTVAL, :staff_name, :role, :shift_timing, :specialization, :assigned_id, :phone1, :phone2)";
                $stid = oci_parse($conn, $sql);
                oci_bind_by_name($stid, ':staff_name', $staffName);
                oci_bind_by_name($stid, ':role', $role);
                oci_bind_by_name($stid, ':shift_timing', $shiftTiming);
                oci_bind_by_name($stid, ':specialization', $specialization);
                oci_bind_by_name($stid, ':assigned_id', $assignedId);
                oci_bind_by_name($stid, ':phone1', $phone1);
                oci_bind_by_name($stid, ':phone2', $phone2);
                $result = oci_execute($stid);
                $response = ['success' => $result, 'message' => $result ? 'Staff added successfully.' : 'Failed to add staff.'];
                break;

            case 'update':
                if ($staffId) {
                    $sql = "UPDATE Staff SET Ch_Name = :staff_name, Ch_Role = :role, CH_Shift_Timings = :shift_timing, 
                            Ch_Specialization = :specialization, Prison_ID = :assigned_id, Ch_Phone1 = :phone1, Ch_Phone2 = :phone2 
                            WHERE Staff_ID = :staff_id";
                    $stid = oci_parse($conn, $sql);
                    oci_bind_by_name($stid, ':staff_id', $staffId);
                    oci_bind_by_name($stid, ':staff_name', $staffName);
                    oci_bind_by_name($stid, ':role', $role);
                    oci_bind_by_name($stid, ':shift_timing', $shiftTiming);
                    oci_bind_by_name($stid, ':specialization', $specialization);
                    oci_bind_by_name($stid, ':assigned_id', $assignedId);
                    oci_bind_by_name($stid, ':phone1', $phone1);
                    oci_bind_by_name($stid, ':phone2', $phone2);
                    $result = oci_execute($stid);
                    $response = ['success' => $result, 'message' => $result ? 'Staff updated successfully.' : 'Failed to update staff.'];
                } else {
                    $response = ['success' => false, 'message' => 'Please select a staff member to update.'];
                }
                break;

            case 'delete':
                if ($staffId) {
                    $success = true;
                    $message = "Staff ID $staffId and all related records deleted successfully.";
                    
                    $stmts = [
                        "DELETE FROM Prisoner_Staff WHERE Staff_ID = :staff_id",
                        "DELETE FROM Staff WHERE Staff_ID = :staff_id"
                    ];

                    foreach ($stmts as $sql) {
                        $stid = oci_parse($conn, $sql);
                        oci_bind_by_name($stid, ':staff_id', $staffId);
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
        $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }

    oci_close($conn);
    // echo json_encode($response);
    echo "<script>
        alert('" . addslashes($response['message']) . "');
        window.location.href = '../php/staff-manage.php';
    </script>";
    exit;
}