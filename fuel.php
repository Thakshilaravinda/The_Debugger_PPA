<?php
$conn = new mysqli("localhost", "root", "", "transport");

// CREATE
if (isset($_POST['add_fuel'])) {
    $name = $_POST['name'];
    $info = $_POST['info'];
    $conn->query("INSERT INTO fuels (name, info) VALUES ('$name', '$info')");
}

// DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM fuels WHERE id=$id");
}

$result = $conn->query("SELECT * FROM fuels");
?>

<h3>Fuel Details</h3>
<form method="POST">
    <input type="text" name="name" placeholder="Name" required>
    <input type="text" name="info" placeholder="Info" required>
    <button type="submit" name="add_fuel">Add</button>
</form>

<table>
<tr><th>Name</th><th>Info</th><th>Action</th></tr>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['name'] ?></td>
    <td><?= $row['info'] ?></td>
    <td><a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this entry?')">Delete</a></td>
</tr>
<?php endwhile; ?>
</table>
