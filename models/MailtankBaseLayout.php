<?php

namespace mailtank\models;

use Yii;
use mailtank\MailtankClient;

/**
 * Class MailtankBaseLayout
 */
class MailtankBaseLayout extends MailtankRecord
{
    const ENDPOINT = '/base_layouts/';

    public $name;
    public $markup;

    protected $crud = [
        'create' => true,
        'read'   => false,
        'update' => false,
        'delete' => true
    ];

    public function rules()
    {
        return [
            [['id', 'markup'], 'safe'],
            [['name', 'markup'], 'required'],
            ['name', 'string', 'max' => 60],
        ];
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
        ]);
    }

    /**
     * Delete base layout
     */
    public function delete()
    {
        $this->url = self::ENDPOINT.$this->id;
        $this->setIsNewRecord(false);
        return parent::delete();
    }
}