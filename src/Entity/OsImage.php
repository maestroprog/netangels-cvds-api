<?php

namespace NetAngels\Entity;

/**
 * Образ NetAngels операционной системы.
 */
class OsImage
{
    private $id;
    private $description;
    private $arch;
    private $requiredData;

    /**
     * @param int $id Идентификатор образа
     * @param string $description Описание образа
     * @param array $arch Возможные архитектуры
     * @param array $requiredData Необходимые данные для заполнения при создании ВДС:
     *    domain - имя домена
     *    email - валидный адрес электронной почты
     *    password - пароль для входа в систему
     *    title - заголовок сайта
     */
    public function __construct($id, $description, array $arch, array $requiredData)
    {
        $this->id = $id;
        $this->description = $description;
        $this->arch = $arch;
        $this->requiredData = $requiredData;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function getArch()
    {
        return $this->arch;
    }

    /**
     * @return array
     */
    public function getRequiredData()
    {
        return $this->requiredData;
    }
}
