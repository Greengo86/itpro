<?php
//открываем сессию
session_start();
// переменная в которую будем сохранять результат работы
$data['result']='error';
// функция для проверки длины строки
function validStringLength($string,$min,$max) {
    $length = mb_strlen($string,'UTF-8');
    if (($length<$min) || ($length>$max)) {
        return false;
    }
    else {
        return true;
    }
}
// если данные были отправлены методом POST, то...
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // устанавливаем результат, равный success
    $data['result']='success';
    //получить имя, которое ввёл пользователь
    if (isset($_POST['name'])) {
        $name = $_POST['name'];
        if (!validStringLength($name,2,30)) {
            $data['name']='Поля имя содержит недопустимое количество символов.';
            $data['result']='error строчка 25';
        }
    } else {
        $data['result']='error';
    }
    //получить email, которое ввёл пользователь
    if (isset($_POST['email'])) {
        $email = $_POST['email'];
        if (!filter_var($email,FILTER_VALIDATE_EMAIL)) {
            $data['email']='Поле email введено неправильно';
            $data['result']='error строчка35';
        }
    } else {
        $data['result']='error строчка38';
    }
    //получить сообщение, которое ввёл пользователь
    if (isset($_POST['message'])) {
        $message = $_POST['message'];
        if (!validStringLength($message,20,500)) {
            $data['message']='Поле сообщение содержит недопустимое количество символов.';
            $data['result']='error строчка45';
        }
    } else {
        $data['result']='error строчка48';
    }
    //получить капчу, которую ввёл пользователь
    if (isset($_POST['captcha'])) {
        $captcha = $_POST['captcha'];
    } else {
        $data['result']='error строчка54';
    }
    // если не существует ни одной ошибки, то прододжаем...
    if ($data['result']=='success') {
        //если пользователь правильно ввёл капчу
        if ($_SESSION["code"] != $captcha) {
            // пользователь ввёл неправильную капчу
            $data['result']='invalidCaptcha';
        }
    }
} else {
    //данные не были отправлены методом пост
    $data['result']='error строчка66';
}

// дальнейшие действия (ошибок не обнаружено)
if ($data['result']=='success') {
    //1. Сохрание формы в файл
    $output = "---------------------------------" . "\n";
    $output .= date("d-m-Y H:i:s") . "\n";
    $output .= "Имя пользователя: " . $name . "\n";
    $output .= "Адрес email: " . $email . "\n";
    $output .= "Сообщение: " . $message . "\n";
    if (file_put_contents(dirname(__FILE__).'/message.txt', $output, FILE_APPEND | LOCK_EX)) {
        $data['result']='success';
    } else {
        $data['result']='error строчка80';
    }
    //2. Отправляем на почту
    // включить файл PHPMailerAutoload.php
    require_once dirname(__FILE__) . '/phpmailer/PHPMailerAutoload.php';
    //формируем тело письма
    $output = "Дата: " . date("d-m-Y H:i") . "\n";
    $output .= "Имя пользователя: " . $name . "\n";
    $output .= "Адрес email: " . $email . "\n";
    $output .= "Сообщение: " . "\n" . $message . "\n";
    // создаём экземпляр класса PHPMailer
    $mail = new PHPMailer;
    $mail->CharSet = 'UTF-8';
    $mail->From      = 'it-nnPRO.ru';
    $mail->FromName  = 'Имя сайта';
    $mail->Subject   = 'Сообщение с формы обратной связи';
    $mail->Body      = $output;
    $mail->addAddress('it-nnpro@yandex.ru');
    // отправляем письмо
    if ($mail->send()) {
        $data['result']='success';
    } else {
        $data['result']='error строчка102';
    }
}
// формируем ответ, который отправим клиенту
echo json_encode($data);
?>