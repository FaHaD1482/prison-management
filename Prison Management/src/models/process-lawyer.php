<?php
include '../config/db_config.php';

$conn = getConnection();
if (!$conn) {
    echo "<script>
        alert('Database connection failed.');
        window.location.href = '../php/lawyer.php';
    </script>";
    exit;
}

$response = ['success' => false, 'message' => 'Unknown action'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $lawyerId = $_POST['lawyerId'] ?? '';
    $lawyerName = $_POST['lawyerName'] ?? '';
    $specialization = $_POST['specialization'] ?? '';
    $firmName = $_POST['firmName'] ?? '';
    $phone = $_POST['phone'] ?? '';

    try {
        oci_execute(oci_parse($conn, 'BEGIN DBMS_OUTPUT.ENABLE; END;'));

        switch ($action) {
            case 'add':
                $sql = "INSERT INTO Lawyer (Lawyer_ID, L_Name, L_Specialization, Firm_Name, L_phone) 
                        VALUES (lawyer_id_seq.NEXTVAL, :lawyer_name, :specialization, :firm_name, :phone)";
                $stid = oci_parse($conn, $sql);
                oci_bind_by_name($stid, ':lawyer_name', $lawyerName);
                oci_bind_by_name($stid, ':specialization', $specialization);
                oci_bind_by_name($stid, ':firm_name', $firmName);
                oci_bind_by_name($stid, ':phone', $phone);
                $result = oci_execute($stid);
                $response = ['success' => $result, 'message' => $result ? 'Lawyer added successfully.' : 'Failed to add lawyer.'];
                break;

            case 'update':
                if ($lawyerId) {
                    $sql = "UPDATE Lawyer SET L_Name = :lawyer_name, L_Specialization = :specialization, 
                            Firm_Name = :firm_name, L_phone = :phone 
                            WHERE Lawyer_ID = :lawyer_id";
                    $stid = oci_parse($conn, $sql);
                    oci_bind_by_name($stid, ':lawyer_id', $lawyerId);
                    oci_bind_by_name($stid, ':lawyer_name', $lawyerName);
                    oci_bind_by_name($stid, ':specialization', $specialization);
                    oci_bind_by_name($stid, ':firm_name', $firmName);
                    oci_bind_by_name($stid, ':phone', $phone);
                    $result = oci_execute($stid);
                    $response = ['success' => $result, 'message' => $result ? 'Lawyer updated successfully.' : 'Failed to update lawyer.'];
                } else {
                    $response = ['success' => false, 'message' => 'Please select a lawyer to update.'];
                }
                break;

            case 'delete':
                if ($lawyerId) {
                    $success = true;
                    $message = "Lawyer ID $lawyerId and all related records deleted successfully.";
                    
                    $stmts = [
                        "DELETE FROM Prisoner_Lawyer WHERE Lawyer_ID = :lawyer_id",
                        "DELETE FROM Lawyer_Case WHERE Lawyer_ID = :lawyer_id",
                        "DELETE FROM Lawyer WHERE Lawyer_ID = :lawyer_id"
                    ];

                    foreach ($stmts as $sql) {
                        $stid = oci_parse($conn, $sql);
                        oci_bind_by_name($stid, ':lawyer_id', $lawyerId);
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
                    $response = ['success' => false, 'message' => 'Please select a lawyer to delete.'];
                }
                break;
        }
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }

    oci_close($conn);
    echo "<script>
        alert('" . addslashes($response['message']) . "');
        window.location.href = '../php/lawyer.php';
    </script>";
    exit;
}
?>