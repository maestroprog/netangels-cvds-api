# Работа с NetAngels CVDS API

Данная библиотека, написанная на PHP, позволяет работать с NetAngels Cloud VDS API.

Она позволяет выполнять почти все операции, описанные в [документации](http://api.netangels.ru/cvds/).

# Принцип работы

Библиотека поделена на классы для работы с разными типами ресурсов API:

* [VmApi](src/Section/VmApi.php) - реализует управление облачными VDS
* [DiskApi](src/Section/DiskApi.php) - реализует управление облачными дисками
* [VmsIpApi](src/Section/VmsIpApi.php) - получает информацию об IP адресах используемых облачными VDS
* [TariffsApi](src/Section/TariffsApi.php) - позволяет получить список доступных тарифов
* [OsApi](src/Section/OsApi.php) - даёт информацию о возможных образах операционных систем для создания облачных VDS

Основной класс [Api](src/Api.php) содержит получение авторизационного токена, и даёт доступ к остальным API классам

Сущности `Vds` и `Disk` инкапсулируют логику для работы с API

## Примеры

> Для работы с API нужен специальный API ключ, получить его можно в [панели NetAngels](https://panel.netangels.ru/api_keys/) будучи авторизованным пользователем с подключенной услугой "Облачные VDS".

1. Инициализация библиотеки и получение авторизационного токена.
    ```php
    define('API_KEY', '#Ваш API ключ#');
    
    try {
        $netAngelsApi = new \NetAngels\Api(new ApiKey(API_KEY));
    } catch (Exception $e) {
        echo '<p>Не удалось получить авторизационный токен:' . $e->getMessage() . '</p>';
        if ($prev = $e->getPrevious()) {
            echo '<p>' . nl2br($prev->getMessage()) . '</p>';
        }
    }
    ```
    
    _Далее переменная `$netAngelsApi` будет использована взеде._
    
    > При создании нового объекта `\NetAngels\Api` происходит попытка получения авторизационного токена.
    Не все API методы требуют авторизационный токен, поэтому можно поймать исключение, и работать дальше с API без токена.
    API ресурсы, поддерживающе работу без токена: `vm-tariffs`, `os-images`, `token` (получение токена).

2. Создание облачной VDS
    
    ```php
    // в качестве примера выберем самый начальный тариф
    $tariff = $netAngelsApi->getTariffsApi()->getLowTariff();
    
    // получаем список возможных образов операционных систем, просматриваем его, и выбираем нужный образ
    $images = $netAngelsApi->getOsApi()->getList();
    
    // затем нужно выбрать образ диска с операционной системой
    // допустим, это будет образ с Debian 7 Wheezy LAMP, и архитектурой 64-битной
    $image = \NetAngels\ValueObject\VmImage::custom(2, 64);
 
    // также для облачной VDS потребуется диск, будем создавать SSD диск на 10ГБ
   $disk = new \NetAngels\Entity\SsdDisk($netAngelsApi, 10);
    
    // создаем обланую вдс на основе выбранного образа
    $requisites = (new \NetAngels\Entity\Vds($netAngelsApi,$tariff))->createByImage($image);
    ```
    _После данных манипуляций в переменной `$requisites`
    будет находиться объект класса `\NetAngels\ValueObject\VmRequisites`;
    после создания новой облачной VDS данный объект хранит IP адрес созданной VDS;
    а если облачная VDS была создана на основе образа ОС,
    то этот объект будет хранить также реквизиты пользователей для доступа к данной VDS._
    
    Данный код
    ```php
    $users = $requisites->getUsers();
    var_dump($users);
    ```
    Выведет что-то вроде этого:
    ```textmate
    object(NetAngels\ValueObject\VmRequisites)[39]
      private 'ip' => string '91.226.83.194' (length=13)
      private 'users' => 
        array (size=2)
          0 => 
            object(NetAngels\ValueObject\VmUser)[41]
              private 'username' => string 'root' (length=4)
              private 'password' => string 'M*************y' (length=15)
          1 => 
            object(NetAngels\ValueObject\VmUser)[40]
              private 'username' => string 'web' (length=3)
              private 'password' => string 'r*************J' (length=15)
    ```
    
3. Получение VDS по ID

    ```php
    $vds = $netAngelsApi->getVmApi()->getVds(777);
    ```
    В сущностях `\Netangels\Entity\Vds` и `\Netangels\Entity\*Disk\` инкапсулировано API,
    поэтому операции можно производить прямо с ними:
    ```
    // переименование VDS
    $vds = $vds->rename('Новое имя');
    // переменной $vds присваивается новый объект VDS с новым именем
    
    // меняем имя и тариф
   
    $vds = $netAngelsApi->getVmApi()->getVds(777)
        ->changeTariff($netAngelsApi->getTariffsApi()->getPowerfulTariff())
        ->rename('Супер-мощная VDS');
    ```

4. Простенькая панель для включения/выключения VDS

    Находится в [примере](sample/index.php) 