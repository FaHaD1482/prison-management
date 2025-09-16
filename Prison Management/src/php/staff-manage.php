<?php
include '../config/db_config.php';

$conn = getConnection();
if (!$conn) {
    die("Database connection failed.");
}

// Fetch staff data
$sql_staff = "SELECT Staff_ID, Ch_Name, Ch_Role, CH_Shift_Timings, Ch_Specialization, Prison_ID, Ch_Phone1, Ch_Phone2 FROM Staff ORDER BY Staff_ID";
$stid_staff = oci_parse($conn, $sql_staff);
oci_execute($stid_staff);

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prison Management</title>
    <link rel="stylesheet" href="../../public/css/staff-manage.css">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check for db_success parameter and show alert
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('db_success') && urlParams.get('db_success') === 'true') {
                alert("db connection success");
            }

            // Navigation click handling
            const buttons = document.querySelectorAll('.sidebar button:not(.logout)');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    buttons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    window.location.href = this.textContent.toLowerCase().replace(' ', '-') + '.php';
                });
            });

            // Log Out button
            document.querySelector('.logout').addEventListener('click', function() {
                window.location.href = '../../public/index.html';
            });

            // Select staff and populate input fields
            window.selectStaff = function(container) {
                const idItem = container.getElementsByClassName('staff-id-item')[0];
                const assignedIdItem = container.getElementsByClassName('staff-id-item')[1];
                const items = container.getElementsByClassName('staff-info-item');

                document.getElementById('staffId').value = idItem.textContent.split(': ')[1];
                document.getElementById('assignedId').value = assignedIdItem.textContent.split(': ')[1];
                document.getElementById('staffName').value = items[0].textContent.split(': ')[1];
                document.getElementById('role').value = items[1].textContent.split(': ')[1];
                document.getElementById('shiftTiming').value = items[2].textContent.split(': ')[1];
                document.getElementById('specialization').value = items[3].textContent.split(': ')[1];
                document.getElementById('phone1').value = items[4].textContent.split(': ')[1];
                document.getElementById('phone2').value = items[5].textContent.split(': ')[1];
            };

            // Search functionality
            window.searchStaff = function() {
                const input = document.getElementById('searchInput').value.toLowerCase();
                const containers = document.querySelectorAll('.staff-info-container');

                containers.forEach(container => {
                    let found = false;
                    const items = container.getElementsByClassName('staff-info-item');
                    for (let item of items) {
                        if (item.textContent.toLowerCase().includes(input)) {
                            found = true;
                            break;
                        }
                    }
                    container.style.display = found ? 'block' : 'none';
                });
            };

            // Add staff
            window.addStaff = function() {
                if (confirm("Are you sure you want to add this staff member?")) {
                    document.getElementById('action').value = 'add';
                    document.getElementById('staffForm').submit();
                }
            };

            // Update staff
            window.updateStaff = function() {
                const staffId = document.getElementById('staffId').value;
                if (staffId && confirm("Are you sure you want to update this staff member?")) {
                    document.getElementById('action').value = 'update';
                    document.getElementById('staffForm').submit();
                } else {
                    alert("Please select a staff member to update.");
                }
            };

            // Delete staff
            window.deleteStaff = function() {
                const staffId = document.getElementById('staffId').value;
                if (staffId && confirm(`Are you sure you want to delete Staff ID: ${staffId}?`)) {
                    document.getElementById('action').value = 'delete';
                    document.getElementById('staffForm').submit();
                } else {
                    alert("Please select a staff member to delete.");
                }
            };

            // Associate staff (placeholder)
            window.clearInput = function() {
                document.getElementById('staffName').value = '';
                document.getElementById('role').value = '';
                document.getElementById('shiftTiming').value = '';
                document.getElementById('specialization').value = '';
                document.getElementById('phone1').value = '';
                document.getElementById('phone2').value = '';
                document.getElementById('assignedId').value = '';
            };
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <img src="../../public/images/user-profile.jpg" alt="User Profile" class="profile-img">
            <button onclick="window.location.href='overview.php'">Overview</button>
            <button onclick="window.location.href='prison-manage.php'">Prison Manage</button>
            <button onclick="window.location.href='prisoner-manage.php'">Prisoner Manage</button>
            <button onclick="window.location.href='staff-manage.php'" class="active">Staff Manage</button>
            <button onclick="window.location.href='warden-manage.php'">Warden Manage</button>
            <button onclick="window.location.href='lawyer.php'">Lawyer</button>
            <button class="logout">Log Out</button>
        </div>
        <div class="content">
            <div class="input-section">
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Search by Name or Role">
                    <button onclick="searchStaff()">Search</button>
                </div>
                <form id="staffForm" method="POST" action="../models/process-staff.php" class="form-group">
                    <input type="hidden" name="action" id="action">
                    <input type="hidden" name="staffId" id="staffId">
                    <input type="text" name="staffName" id="staffName" placeholder="Enter Staff Name">
                    <input type="text" name="role" id="role" placeholder="Enter Role">
                    <input type="text" name="shiftTiming" id="shiftTiming" placeholder="Enter Shift Timing">
                    <input type="text" name="specialization" id="specialization" placeholder="Enter Specialization">
                    <input type="text" name="phone1" id="phone1" placeholder="Enter Primary Phone Number">
                    <input type="text" name="phone2" id="phone2" placeholder="Enter Secondary Phone Number">
                    <input type="number" name="assignedId" id="assignedId" placeholder="Enter Assigned Prison ID">
                </form>
                <div class="action-buttons">
                    <button type="button" onclick="addStaff()" class="add-button">Add Staff</button>
                    <button type="button" onclick="updateStaff()" class="update-button">Update Staff Info</button>
                    <button type="button" onclick="deleteStaff()" class="delete-button">Delete Staff Info</button>
                    <button type="button" onclick="clearInput()" class="clear-button">Clear All Inputs</button>
                </div>
            </div>
            <div class="staff-info">
                <?php
                while ($row = oci_fetch_array($stid_staff, OCI_ASSOC)) {
                    echo "<div class='staff-info-container' onclick=\"selectStaff(this)\">";
                    echo "<div class='staff-id'>";
                    echo "<div class='staff-id-item'><strong>Staff ID: " . htmlspecialchars($row['STAFF_ID']) . "</strong></div>";
                    echo "<div class='staff-id-item'><strong>Assigned Location ID: " . htmlspecialchars($row['PRISON_ID']) . "</strong></div>";
                    echo "</div>";
                    echo "<div class='staff-infos'>";
                    echo "<div class='staff-info-item'><strong>Name:</strong> " . htmlspecialchars($row['CH_NAME']) . "</div>";
                    echo "<div class='staff-info-item'><strong>Role:</strong> " . htmlspecialchars($row['CH_ROLE']) . "</div>";
                    echo "<div class='staff-info-item'><strong>Shift Timing:</strong> " . htmlspecialchars($row['CH_SHIFT_TIMINGS']) . "</div>";
                    echo "<div class='staff-info-item'><strong>Specialization:</strong> " . htmlspecialchars($row['CH_SPECIALIZATION']) . "</div>";
                    echo "<div class='staff-info-item'><strong>Primary Phone:</strong> " . htmlspecialchars($row['CH_PHONE1']) . "</div>";
                    echo "<div class='staff-info-item'><strong>Secondary Phone:</strong> " . htmlspecialchars($row['CH_PHONE2']) . "</div>";
                    echo "</div>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>