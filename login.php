<?php
include_once "../conf.php";

$cookie_token = null;
if (isset($_COOKIE['token'])) {
    $cookie_token = $_COOKIE['token'];
}

/* Проверка авторизации */
$isAuth = false;
$mysqli = new mysqli($sqlhost, $sqluser, $sqlpass, $sqldbname);
$query = "SELECT username, date_reg, token FROM users WHERE token='$cookie_token' ORDER BY date_reg DESC LIMIT 1";
if (mysqli_connect_errno()) {
    printf("Подключение к серверу MySQL невозможно. Код ошибки: %s\n", mysqli_connect_error());
    exit;
}
if ($result = $mysqli->query($query)) {
    $row = $result->fetch_assoc();
    if ($row['username'] != '') {
        $isAuth = true;
    }
    $result->close();
}
$mysqli->close();
/* Окончание проверки авторизации */

/* Обработка полученных из формы данных */
if (isset($_POST['login'])) $username = $_POST['login'];
if (isset($_POST['pass'])) $password = $_POST['pass'];

$isAuth = false;

if (isset($username) && isset($password)) {

    // Поиск данных в БД
    $mysqli = new mysqli($sqlhost, $sqluser, $sqlpass, $sqldbname);
    $query = "SELECT id, username, email, passmd5 FROM users WHERE username='$username' OR email='$username' ORDER BY date_reg DESC LIMIT 1";

    if (mysqli_connect_errno()) {
        $msg = "Подключение к серверу MySQL невозможно. Код ошибки: %s\n" . mysqli_connect_error();
        exit;
    }

    if ($result = $mysqli->query($query)) {

        $row = $result->fetch_row();
        if (password_verify($password, $row[3])) {
            
            $id = $row[0];
            $token = hash('md5', time() + strlen($username));

            $mysqli2 = new mysqli($sqlhost, $sqluser, $sqlpass, $sqldbname);
            $query = "UPDATE users SET token='$token' WHERE id='$id';";
            if (mysqli_connect_errno()) {
                $msg = "Подключение к серверу MySQL невозможно. Код ошибки: %s\n" . mysqli_connect_error();
                exit;
            }

            if ($mysqli2->query($query)) {
                setcookie("token", $token, time() + 604800);
                $isAuth = true;
            } else {
                $msg = "Ошибка записи в БД";
            }
            $mysqli2->close();
        } else {
            $msg = "Пароль не верен";
        }

        $result->close();
    }
    $mysqli->close();
}/* Конец обработки */


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo ($title); ?></title>
    <link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../css/main.css">
    <link href="https://fonts.googleapis.com/css?family=Krub:400,600&display=swap" rel="stylesheet">
</head>

<body>

    <?php

    
    ?>





    <?php if ($isAuth) :  ?>
        <?php
        echo "Вы успешно авторизовались $cookie_token";
        //echo "<script type='text/javascript'> document.location = 'main.php'; </script>"; 
        ?>
    <?php else : ?>
        <?php
        echo "Вы не авторизованы";
        //echo "<script type='text/javascript'> document.location = 'login.php'; </script>"; 
        ?>

        <!-- форма регистрации templates/login.html -->

        <form class="container__signIn" action="login.php" method="post">
            <h3 class="header__signIn">
                Вход
            </h3>
            <label for="loginField">логин или email</label>
            <input type="text" id="loginField" name="login">
            <label for="Password">пароль</label>
            <input type="password" id="Password" name="pass">
            <a href="#" class="link">Забыли пароль?</a>
            <div class="container__button--primary">
                <button class="button--primary">Войти</button>
                <a href="signup.php" class="link">Регистрация</a>
            </div>
        </form>


        <!-- // -->

    <?php endif ?>
</body>

</html>