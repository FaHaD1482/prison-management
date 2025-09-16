<?php
include '../config/db_config.php';

$conn = getConnection();
if (!$conn) {
    die("Database connection failed.");
}

// Fetch warden data
$sql_staff = "SELECT WARDEN_ID, W_NAME, W_RANK, W_PHONE1, W_PHONE2, RESPONSIBILITIES, PRISON_ID FROM Warden ORDER BY WARDEN_ID";
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
    <link rel="stylesheet" href="../../public/css/warden-manage.css">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check for db_success parameter and show alert
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('db_success') && urlParams.get('db_success') === 'true') {
                alert("db connection success");
            }

            // Log Out button
            document.querySelector('.logout').addEventListener('click', function() {
                window.location.href = '../../public/index.html';
            });

            // Select warden and populate input fields
            window.selectWarden = function(container) {
                const idItem = container.getElementsByClassName('staff-id-item')[0];
                const assignedIdItem = container.getElementsByClassName('staff-id-item')[1];
                const items = container.getElementsByClassName('staff-info-item');

                document.getElementById('formWardenId').value = idItem.textContent.split(': ')[1];
                document.getElementById('wardenName').value = items[0].textContent.split(': ')[1];
                document.getElementById('rank').value = items[1].textContent.split(': ')[1];
                document.getElementById('phone1').value = items[2].textContent.split(': ')[1];
                document.getElementById('phone2').value = items[3].textContent.split(': ')[1];
                document.getElementById('responsibilities').value = items[4].textContent.split(': ')[1];
                document.getElementById('assignedId').value = assignedIdItem.textContent.split(': ')[1];
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

            // Add warden
            window.addWarden = function() {
                event.preventDefault();
                if (confirm("Are you sure you want to add this warden?")) {
                    document.getElementById('action').value = 'add';
                    document.getElementById('wardenForm').submit();
                }
            };

            // Update warden
            window.updateWarden = function() {
                const wardenId = document.getElementById('formWardenId').value;
                if (wardenId && confirm("Are you sure you want to update this warden?")) {
                    document.getElementById('action').value = 'update';
                    document.getElementById('wardenForm').submit();
                } else {
                    alert("Please select a warden to update.");
                }
            };

            // Delete warden
            window.deleteWarden = function() {
                event.preventDefault();
                const wardenId = document.getElementById('formWardenId').value;
                if (wardenId && confirm(`Are you sure you want to delete Warden ID: ${wardenId}?`)) {
                    document.getElementById('action').value = 'delete';
                    document.getElementById('wardenForm').submit();
                } else {
                    alert("Please select a warden to delete.");
                }
            };

            // Clear input fields
            window.clearInput = function() {
                document.getElementById('wardenName').value = '';
                document.getElementById('rank').value = '';
                document.getElementById('phone1').value = '';
                document.getElementById('phone2').value = '';
                document.getElementById('responsibilities').value = '';
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
            <button onclick="window.location.href='staff-manage.php'">Staff Manage</button>
            <button onclick="window.location.href='warden-manage.php'" class="active">Warden Manage</button>
            <button onclick="window.location.href='lawyer.php'">Lawyer</button>
            <button class="logout">Log Out</button>
        </div>
        <div class="content">
            <div class="input-section">
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Search by Name or Rank">
                    <button onclick="searchStaff()">Search</button>
                </div>
                <form id="wardenForm" method="POST" action="../models/process-warden.php" class="form-group">
                    <input type="hidden" name="action" id="action">
                    <input type="hidden" name="wardenId" id="formWardenId">
                    <input type="text" name="wardenName" id="wardenName" placeholder="Enter Warden Name" required>
                    <input type="text" name="rank" id="rank" placeholder="Enter Rank" required>
                    <input type="text" name="phone1" id="phone1" placeholder="Enter Primary Phone Number" required>
                    <input type="text" name="phone2" id="phone2" placeholder="Enter Secondary Phone Number" required>
                    <input type="text" name="responsibilities" id="responsibilities" placeholder="Enter Responsibilities" required>
                    <input type="number" name="assignedId" id="assignedId" placeholder="Enter Assigned Prison ID" required>
                </form>
                <div class="action-buttons">
                    <button type="button" onclick="addWarden()" class="add-button">Add Warden</button>
                    <button type="button" onclick="updateWarden()" class="update-button">Update Warden Info</button>
                    <button type="button" onclick="deleteWarden()" class="delete-button">Delete Warden Info</button>
                    <button type="button" onclick="clearInput()" class="clear-button">Clear Inputs</button>
                </div>
            </div>
            <div class="staff-info">
                <?php
                while ($row = oci_fetch_array($stid_staff, OCI_ASSOC)) {
                    echo "<div class='staff-info-container' onclick=\"selectWarden(this)\">";
                    echo "<div class='staff-id'>";
                    echo "<div class='staff-id-item'><strong>Warden ID: " . htmlspecialchars($row['WARDEN_ID']) . "</strong></div>";
                    echo "<div class='staff-id-item'><strong>Assigned Location ID: " . htmlspecialchars($row['PRISON_ID']) . "</strong></div>";
                    echo "</div>";
                    echo "<div class='staff-infos'>";
                    echo "<div class='staff-info-item'><strong>Name:</strong> " . htmlspecialchars($row['W_NAME']) . "</div>";
                    echo "<div class='staff-info-item'><strong>Rank:</strong> " . htmlspecialchars($row['W_RANK']) . "</div>";
                    echo "<div class='staff-info-item'><strong>Primary Phone Number:</strong> " . htmlspecialchars($row['W_PHONE1']) . "</div>";
                    echo "<div class='staff-info-item'><strong>Secondary Phone Number:</strong> " . htmlspecialchars($row['W_PHONE2']) . "</div>";
                    echo "<div class='staff-info-item'><strong>Responsibilities:</strong> " . htmlspecialchars($row['RESPONSIBILITIES']) . "</div>";
                    echo "</div>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>
    </div>
</body>

</html>