<?php

session_name('netangels');
session_start() or die('Cannot start the session.');

if (isset($_GET['key']) && $_GET['key']) {
    $_SESSION['netangels_key'] = $_GET['key'];
}

if (!isset($_SESSION['netangels_key'])): ?>
    <form>
        <label for="key">Укажите API ключ</label>
        <input type="text" name="key" value="" placeholder="API key code"/>
        <input type="submit" value="Сохранить"/>
    </form>
<?php endif;