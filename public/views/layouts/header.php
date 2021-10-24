<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/views/css/bootstrap.css">
    <link rel="stylesheet" href="/views/css/bootstrap-grid.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <link rel="stylesheet" href="/views/css/main.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title><?=$meta["title"]?></title>
</head>
<body>
<?if($user):?>
    <?php require_once __DIR__ . "/nav/user-top-menu.php";?>
<?else:?>
    <?php require_once __DIR__ . "/nav/top-menu.php";?>
<?endif;?>
<div class="<?=(isset($user) ? "container-fluid" : "container")?>">
    <div class="row">
        <div class="container p-0" id="messages"><?php $notifications->showMessages(); ?></div>