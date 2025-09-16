<?php
include '../config/db_config.php';

$conn = getConnection();
if (!$conn) {
    die("Database connection failed.");
}

// Fetch lawyer data
$sql_lawyer = "SELECT LAWYER_ID, L_NAME, L_SPECIALIZATION, FIRM_NAME, L_PHONE FROM Lawyer ORDER BY LAWYER_ID";
$stid_lawyer = oci_parse($conn, $sql_lawyer);
oci_execute($stid_lawyer);

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prison Management</title>
    <link rel="stylesheet" href="../../public/css/lawyer-manage.css">
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

            // Select lawyer and populate input fields
            window.selectLawyer = function(container) {
                const idItem = container.getElementsByClassName('lawyer-id-item')[0];

                const items = container.getElementsByClassName('lawyer-info-item');

                document.getElementById('lawyerId').value = idItem.textContent.split(': ')[1];

                document.getElementById('lawyerName').value = items[0].textContent.split(': ')[1];
                document.getElementById('specialization').value = items[1].textContent.split(': ')[1];
                document.getElementById('firmName').value = items[2].textContent.split(': ')[1];
                document.getElementById('phone').value = items[3].textContent.split(': ')[1];
            };

            // Search functionality
            window.searchLawyer = function() {
                const input = document.getElementById('searchInput').value.toLowerCase();
                const containers = document.querySelectorAll('.lawyer-info-container');

                containers.forEach(container => {
                    let found = false;
                    const items = container.getElementsByClassName('lawyer-info-item');
                    for (let item of items) {
                        if (item.textContent.toLowerCase().includes(input)) {
                            found = true;
                            break;
                        }
                    }
                    container.style.display = found ? 'block' : 'none';
                });
            };

            // Add lawyer
            window.addLawyer = function() {
                if (confirm("Are you sure you want to add this lawyer?")) {
                    document.getElementById('action').value = 'add';
                    document.getElementById('lawyerForm').submit();
                }
            };

            // Update lawyer
            window.updateLawyer = function() {
                const lawyerId = document.getElementById('lawyerId').value;
                if (lawyerId && confirm("Are you sure you want to update this lawyer?")) {
                    document.getElementById('action').value = 'update';
                    document.getElementById('lawyerForm').submit();
                } else {
                    alert("Please select a lawyer to update.");
                }
            };

            // Delete lawyer
            window.deleteLawyer = function() {
                const lawyerId = document.getElementById('lawyerId').value;
                if (lawyerId && confirm(`Are you sure you want to delete Lawyer ID: ${lawyerId}?`)) {
                    document.getElementById('action').value = 'delete';
                    document.getElementById('lawyerForm').submit();
                } else {
                    alert("Please select a lawyer  to delete.");
                }
            };

            // Clear input fields
            window.clearInput = function() {
                document.getElementById('lawyerName').value = '';
                document.getElementById('specialization').value = '';
                document.getElementById('lawyerId').value = '';
                document.getElementById('firmName').value = '';
                document.getElementById('phone').value = '';
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
            <button onclick="window.location.href='warden-manage.php'">Warden Manage</button>
            <button onclick="window.location.href='lawyer.php'" class="active">Lawyer</button>
            <button class="logout">Log Out</button>
        </div>
        <div class="content">
            <div class="input-section">
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Search by Lawyer Name or Specialization">
                    <button onclick="searchLawyer()">Search</button>
                </div>
                <form id="lawyerForm" method="POST" action="../models/process-lawyer.php" class="form-group">
                    <input type="hidden" name="action" id="action">
                    <input type="hidden" name="lawyerId" id="lawyerId">
                    <input type="text" name="lawyerName" id="lawyerName" placeholder="Enter Lawyer Name">
                    <input type="text" name="specialization" id="specialization" placeholder="Enter Specialization">
                    <input type="text" name="firmName" id="firmName" placeholder="Enter Firm Name">
                    <input type="text" name="phone" id="phone" placeholder="Enter Phone Number">
                </form>
                <div class="action-buttons">
                    <button type="button" onclick="addLawyer()" class="add-button">Add Lawyer</button>
                    <button type="button" onclick="updateLawyer()" class="update-button">Update Lawyer Info</button>
                    <button type="button" onclick="deleteLawyer()" class="delete-button">Delete Lawyer Info</button>
                    <button type="button" onclick="clearInput()" class="clear-button">Clear All Inputs</button>
                </div>
            </div>
            <div class="lawyer-info">
                <?php
                while ($row = oci_fetch_array($stid_lawyer, OCI_ASSOC)) {
                    echo "<div class='lawyer-info-container' onclick=\"selectLawyer(this)\">";

                    echo "<div class='lawyer-id'>";

                    echo "<div class='lawyer-id-item'><strong>Lawyer ID: " . htmlspecialchars($row['LAWYER_ID']) . "</strong></div>";

                    echo "</div>";

                    echo "<div class='lawyer-infos'>";

                    echo "<div class='lawyer-info-item'><strong>Name:</strong> " . htmlspecialchars($row['L_NAME']) . "</div>";
                    echo "<div class='lawyer-info-item'><strong>Specialization:</strong> " . htmlspecialchars($row['L_SPECIALIZATION']) . "</div>";
                    echo "<div class='lawyer-info-item'><strong>Firm Name:</strong> " . htmlspecialchars($row['FIRM_NAME']) . "</div>";
                    echo "<div class='lawyer-info-item'><strong>Phone Number:</strong> " . htmlspecialchars($row['L_PHONE']) . "</div>";

                    echo "</div>";

                    echo "</div>";
                }
                ?>
            </div>
        </div>
    </div>
</body>

</html>