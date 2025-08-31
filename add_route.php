<?php
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vehicle_number = $_POST['vehicle_number'];
    $driver_name = $_POST['driver_name'];
    $trip_from = $_POST['trip_from'];
    $trip_to = $_POST['trip_to'];
    $trip_date = $_POST['trip_date'];
    $more_details = $_POST['more_details'];

    $sql = "INSERT INTO routes (vehicle_number, driver_name, trip_from, trip_to, trip_date, more_details)
            VALUES ('$vehicle_number', '$driver_name', '$trip_from', '$trip_to', '$trip_date', '$more_details')";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../index.html");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>