<?php
include '../config/db_config.php';

$conn = getConnection();
if (!$conn) {
    echo "<script>
        alert('Database connection failed.');
        window.location.href = '../php/prisoner-manage.php';
    </script>";
    exit;
}

$response = ['success' => false, 'message' => 'Unknown action'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $prisonerId = $_POST['prisonerId'] ?? '';
    $prisonerName = $_POST['prisonerName'] ?? '';
    $age = $_POST['age'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $startDate = $_POST['startDate'] ?? '';
    $endDate = $_POST['endDate'] ?? '';
    $crime = $_POST['crime'] ?? '';
    $status = $_POST['status'] ?? '';
    $medicalHistory = $_POST['medicalHistory'] ?? '';
    $prisonId = $_POST['prisonId'] ?? '';
    $cellId = $_POST['cellId'] ?? '';
    $caseManagerId = $_POST['caseManagerId'] ?? '';

    try {
        oci_execute(oci_parse($conn, 'BEGIN DBMS_OUTPUT.ENABLE; END;'));

        switch ($action) {
            case 'add':
                $sql = "INSERT INTO Prisoner (Prisoner_ID, P_Name, Age, Gender, Sentence_Start_Date, Sentence_End_Date, Crime_Type, Status, Medical_History, Prison_ID, Cell_ID, Case_Manager_ID) 
                        VALUES (prisoner_id_seq.NEXTVAL, :prisoner_name, :age, :gender, TO_DATE(:start_date, 'YYYY-MM-DD'), TO_DATE(:end_date, 'YYYY-MM-DD'), :crime, :status, :medical_history, :prison_id, :cell_id, :case_manager_id)";
                $stid = oci_parse($conn, $sql);
                oci_bind_by_name($stid, ':prisoner_name', $prisonerName);
                oci_bind_by_name($stid, ':age', $age);
                oci_bind_by_name($stid, ':gender', $gender);
                oci_bind_by_name($stid, ':start_date', $startDate);
                oci_bind_by_name($stid, ':end_date', $endDate);
                oci_bind_by_name($stid, ':crime', $crime);
                oci_bind_by_name($stid, ':status', $status);
                oci_bind_by_name($stid, ':medical_history', $medicalHistory);
                oci_bind_by_name($stid, ':prison_id', $prisonId);
                oci_bind_by_name($stid, ':cell_id', $cellId);
                oci_bind_by_name($stid, ':case_manager_id', $caseManagerId);
                $result = oci_execute($stid);
                $response = ['success' => $result, 'message' => $result ? 'Prisoner added successfully.' : 'Failed to add prisoner.'];
                break;

            case 'update':
                if ($prisonerId) {
                    $sql = "UPDATE Prisoner SET P_Name = :prisoner_name, Age = :age, Gender = :gender, 
                            Sentence_Start_Date = TO_DATE(:start_date, 'YYYY-MM-DD'), 
                            Sentence_End_Date = TO_DATE(:end_date, 'YYYY-MM-DD'), 
                            Crime_Type = :crime, Status = :status, Medical_History = :medical_history, 
                            Prison_ID = :prison_id, Cell_ID = :cell_id, Case_Manager_ID = :case_manager_id 
                            WHERE Prisoner_ID = :prisoner_id";
                    $stid = oci_parse($conn, $sql);
                    oci_bind_by_name($stid, ':prisoner_id', $prisonerId);
                    oci_bind_by_name($stid, ':prisoner_name', $prisonerName);
                    oci_bind_by_name($stid, ':age', $age);
                    oci_bind_by_name($stid, ':gender', $gender);
                    oci_bind_by_name($stid, ':start_date', $startDate);
                    oci_bind_by_name($stid, ':end_date', $endDate);
                    oci_bind_by_name($stid, ':crime', $crime);
                    oci_bind_by_name($stid, ':status', $status);
                    oci_bind_by_name($stid, ':medical_history', $medicalHistory);
                    oci_bind_by_name($stid, ':prison_id', $prisonId);
                    oci_bind_by_name($stid, ':cell_id', $cellId);
                    oci_bind_by_name($stid, ':case_manager_id', $caseManagerId);
                    $result = oci_execute($stid);
                    $response = ['success' => $result, 'message' => $result ? 'Prisoner updated successfully.' : 'Failed to update prisoner.'];
                } else {
                    $response = ['success' => false, 'message' => 'Please select a prisoner to update.'];
                }
                break;

            case 'delete':
                if ($prisonerId) {
                    $success = true;
                    $message = "Prisoner ID $prisonerId and all related records deleted successfully.";
                    
                    $stmts = [
                        "DELETE FROM Prisoner_Lawyer WHERE Prisoner_ID = :prisoner_id",
                        "DELETE FROM Prisoner_MedicalWorker WHERE Prisoner_ID = :prisoner_id",
                        "DELETE FROM Prisoner_Visitor WHERE Prisoner_ID = :prisoner_id",
                        "DELETE FROM Prisoner_Case WHERE Prisoner_ID = :prisoner_id",
                        "DELETE FROM Prisoner_Staff WHERE Prisoner_ID = :prisoner_id",
                        "DELETE FROM Prisoner WHERE Prisoner_ID = :prisoner_id"
                    ];

                    foreach ($stmts as $sql) {
                        $stid = oci_parse($conn, $sql);
                        oci_bind_by_name($stid, ':prisoner_id', $prisonerId);
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
                    $response = ['success' => false, 'message' => 'Please select a prisoner to delete.'];
                }
                break;
        }
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }

    oci_close($conn);
    echo "<script>
        alert('" . addslashes($response['message']) . "');
        window.location.href = '../php/prisoner-manage.php';
    </script>";
    exit;
}
?>