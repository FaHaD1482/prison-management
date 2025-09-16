<?php
include '../config/db_config.php';

$conn = getConnection();
if (!$conn) {
    die("Database connection failed.");
}

// Fetch prisoner data
$sql_prisoner = "SELECT PRISONER_ID, P_NAME, AGE, GENDER, SENTENCE_START_DATE, SENTENCE_END_DATE, CRIME_TYPE, STATUS, MEDICAL_HISTORY, PRISON_ID, CELL_ID, CASE_MANAGER_ID FROM Prisoner ORDER BY PRISONER_ID";
$stid_prisoner = oci_parse($conn, $sql_prisoner);
oci_execute($stid_prisoner);

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prison Management</title>
    <link rel="stylesheet" href="../../public/css/prisoner-manage.css">
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

            // Select prisoner and populate input fields
            window.selectPrisoner = function(container) {
                const idItem = container.getElementsByClassName('prisoner-id-item')[0];
                const prisonIDtem = container.getElementsByClassName('prisoner-id-item')[1];
                const cellIdItem = container.getElementsByClassName('prisoner-id-item')[2];
                const caseIdItem = container.getElementsByClassName('prisoner-id-item')[3];

                const items = container.getElementsByClassName('prisoner-info-item');

                document.getElementById('prisonerId').value = idItem.textContent.split(': ')[1];
                document.getElementById('prisonId').value = prisonIDtem.textContent.split(': ')[1];
                document.getElementById('cellId').value = cellIdItem.textContent.split(': ')[1];
                document.getElementById('caseManagerId').value = caseIdItem.textContent.split(': ')[1];

                document.getElementById('prisonerName').value = items[0].textContent.split(': ')[1];
                document.getElementById('age').value = items[1].textContent.split(': ')[1];
                document.getElementById('gender').value = items[2].textContent.split(': ')[1];
                document.getElementById('crime').value = items[3].textContent.split(': ')[1];
                document.getElementById('status').value = items[4].textContent.split(': ')[1];
                document.getElementById('medicalHistory').value = items[5].textContent.split(': ')[1];
                document.getElementById('startDate').value = items[6].textContent.split(': ')[1];
                document.getElementById('endDate').value = items[7].textContent.split(': ')[1];
            };

            // Search functionality
            window.searchPrisoner = function() {
                const input = document.getElementById('searchInput').value.toLowerCase();
                const containers = document.querySelectorAll('.prisoner-info-container');

                containers.forEach(container => {
                    let found = false;
                    const items = container.getElementsByClassName('prisoner-info-item');
                    for (let item of items) {
                        if (item.textContent.toLowerCase().includes(input)) {
                            found = true;
                            break;
                        }
                    }
                    container.style.display = found ? 'block' : 'none';
                });
            };

            // Add prisoner
            window.addPrisoner = function() {
                if (confirm("Are you sure you want to add this prisoner?")) {
                    document.getElementById('action').value = 'add';
                    document.getElementById('prisonerForm').submit();
                }
            };

            // Update prisoner
            window.updatePrisoner = function() {
                const prisonerId = document.getElementById('prisonerId').value;
                if (prisonerId && confirm("Are you sure you want to update this prisoner?")) {
                    document.getElementById('action').value = 'update';
                    document.getElementById('prisonerForm').submit();
                } else {
                    alert("Please select a prisoner to update.");
                }
            };

            // Delete prisoner
            window.deletePrisoner = function() {
                const prisonerId = document.getElementById('prisonerId').value;
                if (prisonerId && confirm(`Are you sure you want to delete Prisoner ID: ${prisonerId}?`)) {
                    document.getElementById('action').value = 'delete';
                    document.getElementById('prisonerForm').submit();
                } else {
                    alert("Please select a prisoner  to delete.");
                }
            };

            // Clear input fields
            window.clearInput = function() {
                document.getElementById('prisonerName').value = '';
                document.getElementById('age').value = '';
                document.getElementById('gender').selectedIndex = 0; // Reset to dropdown placeholder
                document.getElementById('startDate').value = '';
                document.getElementById('endDate').value = '';
                document.getElementById('crime').value = '';
                document.getElementById('status').selectedIndex = 0; // Reset to dropdown placeholder
                document.getElementById('medicalHistory').value = '';

                document.getElementById('prisonerId').value = '';
                document.getElementById('prisonId').value = '';
                document.getElementById('cellId').value = '';
                document.getElementById('caseManagerId').value = '';
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
            <button onclick="window.location.href='prisoner-manage.php'" class="active">Prisoner Manage</button>
            <button onclick="window.location.href='staff-manage.php'">Staff Manage</button>
            <button onclick="window.location.href='warden-manage.php'">Warden Manage</button>
            <button onclick="window.location.href='lawyer.php'">Lawyer</button>
            <button class="logout">Log Out</button>
        </div>
        <div class="content">
            <div class="input-section">
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Search by Name or Crime Type">
                    <button onclick="searchPrisoner()">Search</button>
                </div>
                <form id="prisonerForm" method="POST" action="../models/process-prisoner.php" class="form-group">
                    <input type="hidden" name="action" id="action">
                    <input type="hidden" name="prisonerId" id="prisonerId">
                    <input type="text" name="prisonerName" id="prisonerName" placeholder="Enter Prisoner Name">
                    <input type="number" name="age" id="age" placeholder="Enter Age">

                    <select name="gender" id="gender">
                        <option value="" disabled selected>Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>

                    <input type="text" name="startDate" id="startDate" placeholder="Sentence Start Date (YYYY-MM-DD)">
                    <input type="text" name="endDate" id="endDate" placeholder="Sentence End Date (YYYY-MM-DD)">
                    <input type="text" name="crime" id="crime" placeholder="Enter Crime Type">

                    <select name="status" id="status">
                        <option value="" disabled selected>Select Status</option>
                        <option value="Active">Active</option>
                        <option value="Released">Released</option>
                        <option value="Parole">Parole</option>
                    </select>

                    <input type="text" name="medicalHistory" id="medicalHistory" placeholder="Enter Medical History">
                    <input type="number" name="prisonId" id="prisonId" placeholder="Enter Assigned Prison ID">
                    <input type="number" name="cellId" id="cellId" placeholder="Enter Assigned Cell ID">
                    <input type="number" name="caseManagerId" id="caseManagerId" placeholder="Enter Case Manager ID">
                </form>
                <div class="action-buttons">
                    <button type="button" onclick="addPrisoner()" class="add-button">Add Prisoner</button>
                    <button type="button" onclick="updatePrisoner()" class="update-button">Update Prisoner Info</button>
                    <button type="button" onclick="deletePrisoner()" class="delete-button">Delete Prisoner Info</button>
                    <button type="button" onclick="clearInput()" class="clear-button">Clear All Inputs</button>
                </div>
            </div>
            <div class="prisoner-info">
                <?php
                while ($row = oci_fetch_array($stid_prisoner, OCI_ASSOC)) {
                    echo "<div class='prisoner-info-container' onclick=\"selectPrisoner(this)\">";

                    echo "<div class='prisoner-id'>";

                    echo "<div class='prisoner-id-item'><strong>Prisoner ID: " . htmlspecialchars($row['PRISONER_ID']) . "</strong></div>";
                    echo "<div class='prisoner-id-item'><strong> Prison ID: " . htmlspecialchars($row['PRISON_ID']) . "</strong></div>";
                    echo "<div class='prisoner-id-item'><strong>Assigned Cell ID: " . htmlspecialchars($row['CELL_ID']) . "</strong></div>";
                    echo "<div class='prisoner-id-item'><strong>Case Manager ID: " . htmlspecialchars($row['CASE_MANAGER_ID']) . "</strong></div>";

                    echo "</div>";

                    echo "<div class='prisoner-infos'>";

                    echo "<div class='prisoner-info-item'><strong>Name:</strong> " . htmlspecialchars($row['P_NAME']) . "</div>";
                    echo "<div class='prisoner-info-item'><strong>Age:</strong> " . htmlspecialchars($row['AGE']) . "</div>";
                    echo "<div class='prisoner-info-item'><strong>Gender:</strong> " . htmlspecialchars($row['GENDER']) . "</div>";
                    echo "<div class='prisoner-info-item'><strong>Crime:</strong> " . htmlspecialchars($row['CRIME_TYPE']) . "</div>";
                    echo "<div class='prisoner-info-item'><strong>Status:</strong> " . htmlspecialchars($row['STATUS']) . "</div>";
                    echo "<div class='prisoner-info-item'><strong>Medical History:</strong> " . htmlspecialchars($row['MEDICAL_HISTORY']) . "</div>";
                    echo "<div class='prisoner-info-item'><strong>Sentence Start Date:</strong> " . htmlspecialchars($row['SENTENCE_START_DATE']) . "</div>";
                    echo "<div class='prisoner-info-item'><strong>Sentence End Date:</strong> " . htmlspecialchars($row['SENTENCE_END_DATE']) . "</div>";

                    echo "</div>";

                    echo "</div>";
                }
                ?>
            </div>
        </div>
    </div>
</body>

</html>