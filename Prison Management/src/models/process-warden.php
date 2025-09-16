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

    try {
        oci_execute(oci_parse($conn, 'BEGIN DBMS_OUTPUT.ENABLE; END;')); // Enable output for debugging if needed

        switch ($action) {
            case 'add':
                $sql = "INSERT INTO Warden (WARDEN_ID, W_NAME, W_RANK, W_PHONE1, W_PHONE2, RESPONSIBILITIES, PRISON_ID) VALUES (warden_id_seq.NEXTVAL, :w_name, :w_rank, :w_phone1, :w_phone2, :responsibilities, :prison_id)";
                $stid = oci_parse($conn, $sql);
                oci_bind_by_name($stid, ':w_name', $_POST['wardenName']);
                oci_bind_by_name($stid, ':w_rank', $_POST['rank']);
                oci_bind_by_name($stid, ':w_phone1', $_POST['phone1']);
                oci_bind_by_name($stid, ':w_phone2', $_POST['phone2']);
                oci_bind_by_name($stid, ':responsibilities', $_POST['responsibilities']);
                oci_bind_by_name($stid, ':prison_id', $_POST['assignedId']);
                $result = oci_execute($stid);
                $response = ['success' => $result, 'message' => $result ? 'Warden added successfully.' : 'Failed to add warden.'];
                break;

            case 'update':
                $sql = "UPDATE Warden SET W_NAME = :w_name, W_RANK = :w_rank, W_PHONE1 = :w_phone1, W_PHONE2 = :w_phone2, RESPONSIBILITIES = :responsibilities, PRISON_ID = :prison_id WHERE WARDEN_ID = :warden_id";
                $stid = oci_parse($conn, $sql);
                oci_bind_by_name($stid, ':warden_id', $_POST['wardenId']);
                oci_bind_by_name($stid, ':w_name', $_POST['wardenName']);
                oci_bind_by_name($stid, ':w_rank', $_POST['rank']);
                oci_bind_by_name($stid, ':w_phone1', $_POST['phone1']);
                oci_bind_by_name($stid, ':w_phone2', $_POST['phone2']);
                oci_bind_by_name($stid, ':responsibilities', $_POST['responsibilities']);
                oci_bind_by_name($stid, ':prison_id', $_POST['assignedId']);
                $result = oci_execute($stid);
                $response = ['success' => $result, 'message' => $result ? 'Warden updated successfully.' : 'Failed to update warden.'];
                break;

            case 'delete':
                $sql = "DELETE FROM Warden WHERE WARDEN_ID = :warden_id";
                $stid = oci_parse($conn, $sql);
                oci_bind_by_name($stid, ':warden_id', $_POST['wardenId']);
                $result = oci_execute($stid);
                $response = ['success' => $result, 'message' => $result ? 'Warden deleted successfully.' : 'Failed to delete warden.'];
                break;
        }
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }

    oci_close($conn);
    // echo json_encode($response);
    echo "<script>
        alert('" . addslashes($response['message']) . "');
        window.location.href = '../php/warden-manage.php';
    </script>";
    exit;
}