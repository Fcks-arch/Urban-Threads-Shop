<?php
include 'connect.php';

$sql = "SELECT * FROM cart";
$result = $conn->query($sql);

$output = "";
while ($row = $result->fetch_assoc()) {
    $output .= "<tr>";
    $output .= "<td>".$row['product_name']."</td>";
    $output .= "<td>Php ".$row['price']."</td>";
    $output .= "<td>".$row['quantity']."</td>";
    $output .= "<td>
                    <form action='removefromcart.php' method='POST'>
                        <input type='hidden' name='cart_id' value='".$row['id']."'>
                        <button type='submit'>Remove</button>
                    </form>
                </td>";
    $output .= "</tr>";
}
echo $output;
?>
