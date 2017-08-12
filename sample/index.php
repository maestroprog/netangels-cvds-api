<?php

use NetAngels\Api;
use NetAngels\ApiException;
use NetAngels\ApiKey;
use NetAngels\ApiToken;

ini_set('display_errors', true);
error_reporting(E_ALL);
require_once __DIR__ . '/../src/bootstrap.php';
require 'key.php';

try {
    $token = null;
    if (isset($_SESSION['token']) && $_SESSION['token'] instanceof ApiToken) {
        $token = $_SESSION['token'];
    }

    $netAngelsApi = new Api(
        new ApiKey($_SESSION['netangels_key']),
        $token
    );
} catch (Exception $e) {
    echo '<p>Не удалось получить авторизационный токен:' . $e->getMessage() . '</p>';
    if ($prev = $e->getPrevious()) {
        echo '<p>' . nl2br($prev->getMessage()) . '</p>';
    }
    exit;
}
if (isset($_GET['vds']) && isset($_GET['action'])) {
    try {
        switch ($_GET['action']) {
            case 'start':
                $netAngelsApi->getVmApi()->getVds($_GET['vds'])->start();
                break;
            case 'stop':
                $netAngelsApi->getVmApi()->getVds($_GET['vds'])->stop();
                break;
            case 'reboot':
                $netAngelsApi->getVmApi()->getVds($_GET['vds'])->softReboot();
                break;
        }
    } catch (ApiException $e) {
        echo '<p>Не удалось выполнить операцию:' . $e->getMessage() . '</p>';
    }
}

?>
<html>
<head>
    <title>Custom hosting panel</title>
</head>
<body>
<h1>Список облачных VDS</h1>
<table>
    <tr>
        <th>ID</th>
        <th>Название</th>
        <th>Состояние</th>
        <th>Действия</th>
    </tr>
    <?php foreach ($netAngelsApi->getVmApi()->getList() as $vds): ?>
        <tr>
            <td><?= $vds->getId(); ?></td>
            <td><?= htmlspecialchars($vds->getName()); ?></td>
            <td><?= $vds->getState(); ?></td>
            <td>
                <?php switch ($vds->getState()): ?>
<?php case 'stopped': ?>
                        <a href="?vds=<?= $vds->getId() ?>&action=start">Запустить</a>
                        <?php break; ?>
                    <?php case 'active': ?>
                        <a href="?vds=<?= $vds->getId() ?>&action=stop">Остановить</a>
                        <?php break; ?>
                    <?php default: ?>
                        - нет -
                    <?php endswitch; ?>
                <a href="?vds=<?= $vds->getId() ?>&action=reboot">Перезагрузка</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
</body>
</html>
