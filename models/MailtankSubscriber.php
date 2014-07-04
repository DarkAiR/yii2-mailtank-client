<?php
/**
 * Class MailtankSubscriber
 *
 * @property string email
 * @property int id
 * @property array properties
 * @property array tags
 */
class MailtankSubscriber extends MailtankRecord
{
    const ENDPOINT = '/subscribers/';

    protected $properties = null;       // Necessarily NULL, that empty properties worked

    public $email;
    public $tags = array();
    public $does_email_exist = true;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function rules()
    {
        return array(
            array('email', 'email'),
            array('email', 'length', 'max' => 255),
            array('email', 'required'),
            array('does_email_exist', 'boolean'),
            array('id, tags, properties', 'safe'),
        );
    }

    public function setProperties($properties)
    {
        if (is_array($properties)) {
            $this->properties = $properties;
        } else {
            throw new \Exception('Type error');
        }
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function setProperty($key, $value)
    {
        if (is_string($key)) {
            $this->properties[$key] = $value;
        } else {
            throw new \Exception('Type error');
        }
    }

    public function getProperty($key)
    {
        if (isset($this->properties[$key])) {
            return $this->properties[$key];
        } else {
            return false;
        }
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
        assert(is_array($ids) or $ids === 'all');
        $fields = array(
            'action' => 'reassign_tag',
            'data' => array(
                'subscribers' => $ids,
                'tag' => $tag
            )
        );
        Yii::app()->mailtank->sendRequest(
            self::ENDPOINT,
            json_encode($fields),
            'patch'
        );

        return true;
    }
}