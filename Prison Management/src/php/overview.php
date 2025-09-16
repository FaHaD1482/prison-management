<?php
include '../config/db_config.php';

$conn = getConnection();
if (!$conn) {
    die("Database connection failed.");
}

// Fetch statistics
$sql_prisons = "SELECT COUNT(*) FROM Prison";
$stid_prisons = oci_parse($conn, $sql_prisons);
oci_execute($stid_prisons);
$totalPrisons = oci_fetch_array($stid_prisons, OCI_NUM)[0];

$sql_prisoners = "SELECT SUM(Total_Inmates) FROM Prison";
$stid_prisoners = oci_parse($conn, $sql_prisoners);
oci_execute($stid_prisoners);
$totalInmates = oci_fetch_array($stid_prisoners, OCI_NUM)[0];

$sql_visits = "SELECT COUNT(*) FROM Visitor WHERE Approval_Status = 'Approved'";
$stid_visits = oci_parse($conn, $sql_visits);
oci_execute($stid_visits);
$totalVisits = oci_fetch_array($stid_visits, OCI_NUM)[0];

$sql_appeals = "SELECT COUNT(*) FROM Court_Case WHERE Appeal_Status = 'Appealed'";
$stid_appeals = oci_parse($conn, $sql_appeals);
oci_execute($stid_appeals);
$pendingAppeals = oci_fetch_array($stid_appeals, OCI_NUM)[0];

// Fetch prisoner sentencing data for the last 10 years (3700 days)
$days = [];
$labels = [];
$data = [];
$currentDate = date('Y-m-d');
for ($i = 3700; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('Y/m', strtotime($date));
    $days[$date] = 0; // Initialize count for each day
}

$sql_prisoners_by_day = "SELECT TO_CHAR(Sentence_Start_Date, 'YYYY-MM-DD') as sentencing_date, COUNT(*) as count 
                        FROM Prisoner 
                        WHERE Sentence_Start_Date >= TRUNC(SYSDATE) - 3700
                        GROUP BY TO_CHAR(Sentence_Start_Date, 'YYYY-MM-DD')";
$stid_prisoners_by_day = oci_parse($conn, $sql_prisoners_by_day);
oci_execute($stid_prisoners_by_day);

while ($row = oci_fetch_array($stid_prisoners_by_day, OCI_ASSOC)) {
    $date = $row['SENTENCING_DATE'];
    if (isset($days[$date])) {
        $days[$date] = (int)$row['COUNT'];
    }
}

$data = array_values($days);
$labelsJson = json_encode($labels);
$dataJson = json_encode($data);

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prison Management</title>
    <link rel="stylesheet" href="../../public/css/overview.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <img src="../../public/images/user-profile.jpg" alt="User Profile" class="profile-img">
            <button class="active">Overview</button>
            <button>Prison Manage</button>
            <button>Prisoner Manage</button>
            <button>Staff Manage</button>
            <button>Warden Manage</button>
            <button>Lawyer</button>
            <button class="logout">Log Out</button>
        </div>
        <div class="content">
            <div class="stats">
                <div class="stat-box">
                    <div class="stat-title">Total Prisons</div>
                    <div class="stat-number"><?php echo $totalPrisons; ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-title">Total Inmates</div>
                    <div class="stat-number"><?php echo $totalInmates; ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-title">Total Visits</div>
                    <div class="stat-number"><?php echo $totalVisits; ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-title">Pending Appeals</div>
                    <div class="stat-number"><?php echo $pendingAppeals; ?></div>
                </div>
            </div>
            <div class="chart-container">
                <div class="chart-title">Number of Prisoners Sentenced by 10 years</div>
                <canvas id="prisonerChart"></canvas>
            </div>
        </div>
    </div>
    <script>
        // Chart.js configuration
        const ctx = document.getElementById('prisonerChart').getContext('2d');
        const myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo $labelsJson; ?>,
                datasets: [{
                    label: 'Prisoners Sentenced',
                    data: <?php echo $dataJson; ?>,
                    backgroundColor: 'rgba(225, 18, 76, 1)',
                    borderColor: 'rgba(186, 3, 61, 0.55)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Prisoners'
                        },
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Year/Month'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });

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
    </script>
</body>
</html>