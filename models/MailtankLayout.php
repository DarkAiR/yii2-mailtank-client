<?php

namespace mailtank\models;

/**
 * Class MailtankLayout
 */
class MailtankLayout extends \mailtank\BaseActiveRecord
{
    const ENDPOINT = '/layouts/';

    public $name;
    public $markup;
    public $plaintext_markup;
    public $subject_markup;
    public $base;

    protected $crud = array(
        'create' => true,
        'read'   => false,
        'update' => false,
        'delete' => true
    );

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function rules()
    {
        return array(
            array('markup', 'safe'),
            array('name', 'length', 'max' => 60),
            array('name, markup, subject_markup', 'required'),
            array('id, plaintext_markup, base', 'safe'),
        );
    }

    /**
     * Returns the list of attribute names of the model.
     * @return array list of attribute names.
     */
    public function attributes()
    {
        return array_merge_recursive(parent::attributes(), [
            'markup',
            'name',
            'plaintext_markup',
            'subject_markup',
            'base',
        ]);
    }

    public function delete()
    {
        $this->url = self::ENDPOINT.$this->id;
        $this->setIsNewRecord(false);
        return parent::delete();
    }
}