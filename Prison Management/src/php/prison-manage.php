<?php
include '../config/db_config.php';

$conn = getConnection();
if (!$conn) {
    die("Database connection failed.");
}

// Fetch prison data
$sql_prisons = "SELECT Prison_ID, Prison_Name, Location, Type, Capacity, NUMBER_OF_CELLS, TOTAL_INMATES FROM Prison ORDER BY Prison_ID";
$stid_prisons = oci_parse($conn, $sql_prisons);
oci_execute($stid_prisons);

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prison Management</title>
    <link rel="stylesheet" href="../../public/css/prison-manage.css">
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

            // Select row and populate input fields
            window.selectRow = function(row) {
                const cells = row.getElementsByTagName('td');
                document.getElementById('prisonId').value = cells[0].textContent;
                document.getElementById('prisonName').value = cells[1].textContent;
                document.getElementById('location').value = cells[2].textContent;
                document.getElementById('type').value = cells[3].textContent;
                document.getElementById('capacity').value = cells[4].textContent;
                document.getElementById('numberOfCells').value = cells[5].textContent;
                document.getElementById('totalInmates').value = cells[6].textContent;
            };

            // Search functionality
            window.searchPrison = function() {
                const input = document.getElementById('searchInput').value.toLowerCase();
                const table = document.querySelector('.prison-table tbody');
                const rows = table.getElementsByTagName('tr');

                for (let i = 0; i < rows.length; i++) {
                    let found = false;
                    const cells = rows[i].getElementsByTagName('td');
                    for (let j = 0; j < cells.length; j++) {
                        if (cells[j].textContent.toLowerCase().includes(input)) {
                            found = true;
                            break;
                        }
                    }
                    rows[i].style.display = found ? '' : 'none';
                }
            };

            // Add prison
            window.addPrison = function() {
                if (confirm("Are you sure you want to add this prison?")) {
                    document.getElementById('action').value = 'add';
                    document.getElementById('prisonForm').submit();
                }
            };

            // Update prison
            window.updatePrison = function() {
                const prisonId = document.getElementById('prisonId').value;
                if (prisonId && confirm("Are you sure you want to update this prison?")) {
                    document.getElementById('action').value = 'update';
                    document.getElementById('prisonForm').submit();
                } else {
                    alert("Please select a prison to update.");
                }
            };

            // Delete prison
            window.deletePrison = function() {
                const prisonId = document.getElementById('prisonId').value;
                if (prisonId && confirm(`Are you sure you want to delete Prison ID: ${prisonId}?`)) {
                    document.getElementById('action').value = 'delete';
                    document.getElementById('prisonForm').submit();
                } else {
                    alert("Please select a prison to delete.");
                }
            };

            // Clear Inputs (associated Warden and Staffs functionality)
            window.associatedWardenAndStaffs = function() {
                document.getElementById('prisonName').value = '';
                document.getElementById('type').value = '';
                document.getElementById('location').value = '';
                document.getElementById('capacity').value = '';
                document.getElementById('numberOfCells').value = '';
                document.getElementById('totalInmates').value = '';
            };
        });
    </script>
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <img src="../../public/images/user-profile.jpg" alt="User Profile" class="profile-img">
            <button onclick="window.location.href='overview.php'">Overview</button>
            <button class="active">Prison Manage</button>
            <button>Prisoner Manage</button>
            <button>Staff Manage</button>
            <button>Warden Manage</button>
            <button>Lawyer</button>
            <button class="logout">Log Out</button>
        </div>
        <div class="content">
            <div class="input-section">
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Search by Name or Location">
                    <button onclick="searchPrison()">Search</button>
                </div>
                <form id="prisonForm" method="POST" action="../models/process-prison.php" class="form-group">
                    <input type="hidden" name="action" id="action">
                    <input type="hidden" name="prisonId" id="prisonId">
                    <input type="text" name="prisonName" id="prisonName" placeholder="Enter Prison Name">
                    <input type="text" name="type" id="type" placeholder="Enter Prison Type">
                    <input type="text" name="location" id="location" placeholder="Enter Prison Location">
                    <input type="number" name="capacity" id="capacity" min="0" placeholder="Enter Capacity">
                    <input type="number" name="numberOfCells" id="numberOfCells" min="0" placeholder="Enter Number of Cells">
                    <input type="number" name="totalInmates" id="totalInmates" min="0" placeholder="Enter Total Inmates">
                </form>
                <div class="action-buttons">
                    <button type="button" onclick="addPrison()" class="add-button">Add Prison</button>
                    <button type="button" onclick="updatePrison()" class="update-button">Update Prison</button>
                    <button type="button" onclick="deletePrison()" class="delete-button">Delete Prison</button>
                    <button type="button" onclick="associatedWardenAndStaffs()" class="associate-button">Clear Input Fields</button>
                </div>
            </div>
            <div class="table-section">
                <table class="prison-table">
                    <thead>
                        <tr>
                            <th>Prison ID</th>
                            <th>Prison Name</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Capacity</th>
                            <th>Cells</th>
                            <th>Inmates</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = oci_fetch_array($stid_prisons, OCI_ASSOC)) {
                            echo "<tr onclick=\"selectRow(this)\">";
                            echo "<td>" . htmlspecialchars($row['PRISON_ID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['PRISON_NAME']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['LOCATION']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['TYPE']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['CAPACITY']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['NUMBER_OF_CELLS']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['TOTAL_INMATES']) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>