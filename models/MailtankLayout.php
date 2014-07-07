<?php

namespace mailtank\models;

use Yii;
use mailtank\MailtankClient;

/**
 * Class MailtankLayout
 */
class MailtankLayout extends MailtankRecord
{
    const ENDPOINT = '/layouts/';

    public $name;
    public $markup;
    public $plaintext_markup;
    public $subject_markup;
    public $base;

    protected $crud = [
        'create' => true,
        'read'   => false,
        'update' => false,
        'delete' => true
    ];

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
    
    /**
     * Rules
     */
    public function rules()
    {
        return [
            [['name', 'markup', 'subject_markup'], 'required'],
            [['id', 'plaintext_markup', 'base', 'markup'], 'safe'],
        ];
    }

    public function delete()
    {
        $this->url = self::ENDPOINT.$this->id;
        $this->setIsNewRecord(false);
        return parent::delete();
    }
}