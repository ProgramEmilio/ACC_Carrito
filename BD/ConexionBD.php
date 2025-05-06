<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conexion</title>
</head>
<body>
    <?php
    $_servername='localhost:3307';
    $database='ERP_ACC';
    $username='root';
    $password='';
    //create connection
    $conn=mysqli_connect($_servername,$username,$password,$database);
    //check connection
    if(!$conn){
        die("Connection failed: ".mysqli_connect_error());
    }
    //echo "connected succesfully";
    //echo "<br>";
    ?>
</body>
</html>