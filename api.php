<?php
header('Content-Type: application/json');
$conn = mysqli_connect("localhost", "root", "", "gatifin_db");

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action == 'login') {
    $u = $_POST['username'];
    $p = $_POST['password'];
    $res = mysqli_query($conn, "SELECT * FROM users WHERE username='$u' AND password='$p'");
    if (mysqli_num_rows($res) > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}

if ($action == 'register') {
    $u = $_POST['username'];
    $p = $_POST['password'];
    $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$u'");
    if (mysqli_num_rows($check) > 0) {
        echo json_encode(['success' => false, 'message' => 'Username ada']);
    } else {
        mysqli_query($conn, "INSERT INTO users (username, password) VALUES ('$u', '$p')");
        echo json_encode(['success' => true]);
    }
}

if ($action == 'get_data') {
    $u = $_GET['username'];
    $userData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE username='$u'"));
    $trans = mysqli_query($conn, "SELECT * FROM transactions WHERE username='$u' ORDER BY id DESC");
    $list = [];
    while($row = mysqli_fetch_assoc($trans)) {
        $row['id'] = (int)$row['id'];
        $row['amount'] = (int)$row['amount'];
        $list[] = $row;
    }
    echo json_encode(['user' => $userData, 'transactions' => $list]);
}

if ($action == 'save_transaction') {
    $id = $_POST['id'];
    $u = $_POST['username'];
    $t = $_POST['type'];
    $a = $_POST['amount'];
    $c = $_POST['category'];
    $n = $_POST['note'];
    $d = $_POST['date'];
    $q = "INSERT INTO transactions VALUES ('$id', '$u', '$t', '$a', '$c', '$n', '$d')";
    echo json_encode(['success' => mysqli_query($conn, $q)]);
}

if ($action == 'delete_transaction') {
    $id = $_POST['id'];
    mysqli_query($conn, "DELETE FROM transactions WHERE id='$id'");
    echo json_encode(['success' => true]);
}

if ($action == 'clear_all') {
    $u = $_POST['username'];
    mysqli_query($conn, "DELETE FROM transactions WHERE username='$u'");
    echo json_encode(['success' => true]);
}
?>