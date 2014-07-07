<?php

namespace mailtank\models;

use Yii;
use mailtank\MailtankClient;

/**
 * Class MailtankSubscriber
 */
class MailtankSubscriber extends MailtankRecord
{
    const ENDPOINT = '/subscribers/';

    protected $properties = null;       // Necessarily NULL, that empty properties worked

    public $email;
    public $tags = [];
    public $does_email_exist = true;

    public function rules()
    {
        return [
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'required'],
            ['does_email_exist', 'boolean'],
            [['id', 'tags', 'properties'], 'safe'],
        ];
    }

    public function setProperties($properties)
    {
        if (!is_array($properties))
            throw new \Exception('Type error');
        $this->properties = $properties;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function setProperty($key, $value)
    {
        if (!is_string($key))
            throw new \Exception('Type error');    
        $this->properties[$key] = $value;
    }

    public function getProperty($key)
    {
        if (isset($this->properties[$key]))
            return $this->properties[$key];
        return false;
    }


    /**
     * Returns the list of attribute names of the model.
     * @return array list of attribute names.
     */
    public function attributes()
    {
        return array_merge_recursive(parent::attributes(), [
            'email',
            'tags',
            'url',
            'properties',
            'does_email_exist',
        ]);
    }

    /**
     * Reassigns tag to specified subscribers
     * @param int[]|string $ids To assign tag ti all users set $ids === 'all'
     * @param string $tag
     * @return bool
     */
    public static function patchTags($ids, $tag)
    {
        assert(is_array($ids) || $ids === 'all');
        $fields = [
            'action' => 'reassign_tag',
            'data' => [
                'subscribers' => $ids,
                'tag' => $tag
            ]
        ];
        Yii::$app->mailtankClient->sendRequest(
            self::ENDPOINT,
            json_encode($fields),
            'patch'
        );

        return true;
    }
}