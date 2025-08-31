<?php
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'];
    $vehicle_number = $_POST['vehicle_number'];
    $fuel_type = $_POST['fuel_type'];
    $liters = $_POST['liters'];
    $total_value = $_POST['total_value'];

    $sql = "INSERT INTO fuel (date, vehicle_number, fuel_type, liters, total_value)
            VALUES ('$date', '$vehicle_number', '$fuel_type', '$liters', '$total_value')";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../index.html");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>