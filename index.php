
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Transport Management</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="sidebar">
    <h2>Transport Panel</h2>
    <ul>
      <li onclick="showTab('driver')">Driver Details</li>
      <li onclick="showTab('vehicle')">Vehicle Details</li>
      <li onclick="showTab('route')">Route</li>
      <li onclick="showTab('fuel')">Fuel Invoice</li>
    </ul>
  </div>
  <div class="content">
    <div id="driver" class="tab active">
      <?php include('php/driver.php'); ?>
    </div>
    <div id="vehicle" class="tab">
      <?php include('php/vehicle.php'); ?>
    </div>
    <div id="route" class="tab">
      <?php include('php/route.php'); ?>
    </div>
    <div id="fuel" class="tab">
      <?php include('php/fuel.php'); ?>
    </div>
  </div>

  <script>
    function showTab(tabId) {
      document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
      document.getElementById(tabId).classList.add('active');
    }
  </script>
</body>
</html>
