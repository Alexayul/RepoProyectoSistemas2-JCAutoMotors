<!DOCTYPE html>
<html>
<head>
    <title>Cars</title>
</head>
<body>
    <h1>List of Cars</h1>
    <ul>
        <?php foreach ($cars as $car): ?>
            <li><?php echo $car['name']; ?> - <a href="?action=show&id=<?php echo $car['id']; ?>">View Details</a></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
