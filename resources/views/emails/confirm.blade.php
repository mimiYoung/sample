<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>请确认注册</title>
</head>
<body>
    <h1>感谢您在 Sample 网站进行注册！</h1>
    <p>
        请点击下面的链接完成注册：
        <a href="{{ route('confirm_email', $user->activation_token) }}">
            {{ route('confirm_email', $user->activation_token) }}
        </a>
    </p>
    <p>若非本人操作，请忽略此文件。</p>
</body>
</html>