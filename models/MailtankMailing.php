<?php

namespace mailtank\models;

use Yii;
use mailtank\MailtankClient;

/**
 * Class MailtankMailing
 */
class MailtankMailing extends MailtankRecord
{
    const ENDPOINT = '/mailings/';

    public $url;
    public $status;
    public $layout_id;
    public $context;
    public $tags;
    public $tags_union = false;
    public $tags_and_receivers_union = false;
    public $unsubscribe_tags;
    public $unsubscribe_link;
    public $subscribers;
    public $attachments;

    protected $target;
    protected $crud = [
        'create' => true,
        'read'   => true,
        'update' => false,
        'delete' => false
    ];

    public function rules()
    {
        return [
            [['id', 'layout_id', 'context', 'tags', 'subscribers', 'attachments'], 'safe'],
            [['layout_id', 'context'], 'required'],
            [['tags_union', 'tags_and_receivers_union'], 'boolean'],
            [['unsubscribe_link'], 'url'],
            [['unsubscribe_tags'], 'unsubscribeTagValidator'],
        ];
    }

    public function unsubscribeTagValidator($attribute, $params)
    {
        if (empty($this->{$attribute}) && empty($this->unsubscribe_link)) {
            $this->addError($attribute,
                'Unsubscribe tags is required if no unsubscribe link specified');
        }
    }

    /**
     * Returns the list of attribute names of the model.
     * @return array list of attribute names.
     */
    public function attributes()
    {
        return array_merge_recursive(parent::attributes(), [
            'status',
            'tags',
            'tags_union',
            'tags_and_receivers_union',
            'unsubscribe_tags',
            'url',
            'subscribers',
            'layout_id',
            'context',
            'attachments'
        ]);
    }

    private static function moveParam($param, & $fields)
    {
        if (empty($fields[$param]))
            return;

        $fields['target'][$param] = $fields[$param];
        unset($fields[$param]);
    }

    public function beforeSendAttributes($fields)
    {
        self::moveParam('tags', $fields);
        self::moveParam('unsubscribe_tags', $fields);
        self::moveParam('unsubscribe_link', $fields);
        self::moveParam('subscribers', $fields);
        self::moveParam('tags_union', $fields);
        self::moveParam('tags_and_receivers_union', $fields);

        return parent::beforeSendAttributes($fields);
    }
}