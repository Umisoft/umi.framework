<?php
/**
 * UMI.Framework (http://umi-framework.ru/)
 *
 * @link      http://github.com/Umisoft/framework for the canonical source repository
 * @copyright Copyright (c) 2007-2013 Umisoft ltd. (http://umisoft.ru/)
 * @license   http://umi-framework.ru/license/bsd-3 BSD-3 License
 */

namespace umi\form\toolbox\factory;

use umi\form\adapter\IDataAdapter;
use umi\form\element\Button;
use umi\form\element\Checkbox;
use umi\form\element\CSRF;
use umi\form\element\Hidden;
use umi\form\element\html5\Color;
use umi\form\element\html5\Date;
use umi\form\element\html5\DateTime;
use umi\form\element\html5\Email;
use umi\form\element\html5\Month;
use umi\form\element\html5\Number;
use umi\form\element\html5\Phone;
use umi\form\element\html5\Range;
use umi\form\element\html5\Search;
use umi\form\element\html5\Time;
use umi\form\element\html5\Url;
use umi\form\element\html5\Week;
use umi\form\element\IFormElement;
use umi\form\element\CheckboxGroup;
use umi\form\element\MultiSelect;
use umi\form\element\Password;
use umi\form\element\Radio;
use umi\form\element\Reset;
use umi\form\element\Select;
use umi\form\element\Submit;
use umi\form\element\Text;
use umi\form\element\Textarea;
use umi\form\exception\InvalidArgumentException;
use umi\form\exception\OutOfBoundsException;
use umi\form\exception\RuntimeException;
use umi\form\fieldset\FieldSet;
use umi\form\fieldset\IFieldSet;
use umi\form\Form;
use umi\form\IEntityFactory;
use umi\form\IForm;
use umi\form\IFormEntity;
use umi\i18n\TLocalizable;
use umi\toolkit\factory\IFactory;
use umi\toolkit\factory\TFactory;

/**
 * Фабрика элементов формы.
 * Создает обычные элементы формы, а также группы полей и коллекции.
 */
class EntityFactory implements IEntityFactory, IFactory
{

    use TFactory;

    /**
     * @var array $elementTypes типы поддерживаемых элементов
     */
    public $elementTypes = [
        Button::TYPE_NAME        => 'umi\form\element\Button',
        Checkbox::TYPE_NAME      => 'umi\form\element\Checkbox',
        CheckboxGroup::TYPE_NAME => 'umi\form\element\CheckboxGroup',
        Hidden::TYPE_NAME        => 'umi\form\element\Hidden',
        Password::TYPE_NAME      => 'umi\form\element\Password',
        Radio::TYPE_NAME         => 'umi\form\element\Radio',
        Submit::TYPE_NAME        => 'umi\form\element\Submit',
        Text::TYPE_NAME          => 'umi\form\element\Text',
        Textarea::TYPE_NAME      => 'umi\form\element\Textarea',
        CSRF::TYPE_NAME          => 'umi\form\element\CSRF',
        Reset::TYPE_NAME         => 'umi\form\element\Reset',
        Select::TYPE_NAME        => 'umi\form\element\Select',
        MultiSelect::TYPE_NAME   => 'umi\form\element\MultiSelect',
        /*
         * HTML5
         */
        Color::TYPE_NAME         => 'umi\form\element\html5\Color',
        Date::TYPE_NAME          => 'umi\form\element\html5\Date',
        DateTime::TYPE_NAME      => 'umi\form\element\html5\DateTime',
        Email::TYPE_NAME         => 'umi\form\element\html5\Email',
        Month::TYPE_NAME         => 'umi\form\element\html5\Month',
        Number::TYPE_NAME        => 'umi\form\element\html5\Number',
        Phone::TYPE_NAME         => 'umi\form\element\html5\Phone',
        Range::TYPE_NAME         => 'umi\form\element\html5\Range',
        Search::TYPE_NAME        => 'umi\form\element\html5\Search',
        Time::TYPE_NAME          => 'umi\form\element\html5\Time',
        Url::TYPE_NAME           => 'umi\form\element\html5\Url',
        Week::TYPE_NAME          => 'umi\form\element\html5\Week',
    ];

    /**
     * @var array $fieldSetTypes типы поддерживаемых наборов сущностей
     */
    public $fieldSetTypes = [
        Form::TYPE_NAME       => 'umi\form\Form',
        FieldSet::TYPE_NAME   => 'umi\form\fieldset\FieldSet'
    ];

    /**
     * @var array $dataAdapters список адаптеров данных формы
     */
    public $dataAdapters = [
        'array' => 'umi\form\adapter\ArrayFormAdapter',
        'default' => 'umi\form\adapter\DefaultFormAdapter',
        'umi\orm\object\IObject' => 'umi\form\adapter\ObjectFormAdapter',
        'umi\config\entity\IConfig' => 'umi\form\adapter\ConfigFormAdapter',
    ];

    /**
     * {@inheritdoc}
     */
    public function createForm(array $config, $object = null)
    {
        $name = isset($config['name']) ? $config['name'] : '';
        $config['type'] = Form::TYPE_NAME;

        /**
         * @var IForm $form
         */
        $form = $this->createFieldSet($name, $config, ['umi\form\IForm']);
        $form->setDataAdapter($this->createAdapter($object));

        return $form;
    }

    /**
     * Создает элемент формы. Это может быть как просто элемент,
     * так и коллекция элементов.
     * @param string $name имя элемента
     * @param array $config конфигурация элемента, включая аттрибуты и опции
     * @throws RuntimeException если тип элемента не определен
     * @throws OutOfBoundsException если тип элемента не поддерживается
     * @return IFormEntity
     */
    protected function createFormEntity($name, array $config)
    {
        if (!isset($config['type'])) {
            throw new RuntimeException(
                $this->translate(
                    'Cannot create form entity "{name}". Type is unknown.',
                    ['name' => $name]
                )
            );
        }

        $type = $config['type'];

        if (isset($this->elementTypes[$type])) {
            $entity = $this->createElement($name, $config);
        } elseif (isset($this->fieldSetTypes[$type])) {
            $entity = $this->createFieldSet($name, $config);
        } else {
            throw new OutOfBoundsException($this->translate(
                'Form entity type "{type}" is not supported.',
                ['type' => $type]
            ));
        }

        return $entity;
    }

    /**
     * Создает элемент формы.
     * @param string $name имя
     * @param array $config конфигурация
     * @return IFormElement
     */
    protected function createElement($name, array $config)
    {

        $type = $config['type'];
        $className = isset($config['className']) ? $config['className'] : $this->elementTypes[$type];

        $attributes = isset($config['attributes']) ? $config['attributes'] : [];
        $options = isset($config['options']) ? $config['options'] : [];

        /**
         * @var IFormElement $element
         */
        $element = $this->getPrototype(
            $className,
            ['umi\form\element\IFormElement']
        )
        ->createInstance([$name, $attributes, $options]);

        if (isset($config['label'])) {
            $element->setLabel($config['label']);
        }

        return $element;

    }

    /**
     * Создает группу сущностей.
     * @param string $name имя
     * @param array $config конфигурация
     * @param array $contracts список контрактов
     * @return IFieldSet
     */
    protected function createFieldSet($name, array $config, array $contracts = ['umi\form\fieldset\IFieldSet'])
    {
        $type = $config['type'];
        $className = isset($config['className']) ? $config['className'] : $this->fieldSetTypes[$type];

        $attributes = isset($config['attributes']) ? $config['attributes'] : [];
        $options = isset($config['options']) ? $config['options'] : [];

        /**
         * @var IFieldSet $fieldSet
         */
        $fieldSet = $this->getPrototype($className, $contracts)
            ->createInstance([$name, $attributes, $options]);

        if (isset($config['elements']) && is_array($config['elements'])) {
            foreach($config['elements'] as $name => $elementConfig) {
                $fieldSet->add($this->createFormEntity($name, $elementConfig));
            }
        }

        if (isset($config['label'])) {
            $fieldSet->setLabel($config['label']);
        }

        return $fieldSet;
    }

    /**
     * Выбирает и создает адаптер для данных, ассоциированных с формой.
     * @param mixed $object данные
     * @return IDataAdapter адаптер
     */
    protected function createAdapter($object)
    {
        return $this->getPrototype(
            $this->selectAdapter($object),
            ['umi\form\adapter\IDataAdapter']
        )
            ->createInstance([$object]);
    }

    /**
     * Выбирает класс адаптера для данных.
     * @param mixed $object данные
     * @throws InvalidArgumentException если адаптер не был выбран.
     * @return string класс выбранного адаптера
     */
    protected function selectAdapter($object)
    {
        if (is_null($object)) {
            return $this->dataAdapters['default'];
        }
        if (is_array($object)) {
            return $this->dataAdapters['array'];
        }

        foreach ($this->dataAdapters as $interface => $adapter) {
            if ($object instanceof $interface) {
                return $adapter;
            }
        }

        throw new InvalidArgumentException($this->translate(
            'Form data adapter not found for objects({type}).',
            ['type' => is_object($object) ? get_class($object) : gettype($object)]
        ));
    }

}