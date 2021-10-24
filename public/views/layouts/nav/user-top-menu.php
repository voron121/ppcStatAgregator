<div class="rox-fluid top-menu">
    <div class="container">
        <div class="row">
            <div class="col-sm-1 p-0">
                <a href="/">
                    <img src="views/images/logo.jpg" alt="PPCSoft" width="35">
                </a>
            </div>
            <div class="col-sm pl-0">
                <nav class="nav">
                    <a class="nav-link" href="/accounts-list.php">Аккаунты</a>
                    <a class="nav-link" href="/products-list.php">Товары</a>
                    <a class="nav-link" href="/campaigns-list.php">Кампании</a>
                    <a class="nav-link" href="/ad-groups-list.php">Группы</a>
                    <a class="nav-link" href="/ads-list.php">Объявления</a>
                </nav>
            </div>
            <div class="col-sm-2 pr-0">
                <nav class="nav justify-content-end">
                    <a class="nav-link" href="#"><?=$user->getLogin();?></a>
                    <a class="nav-link" href="/auth.php?action=logout">Выйти</a>
                </nav>
            </div>
        </div>
    </div>
</div>