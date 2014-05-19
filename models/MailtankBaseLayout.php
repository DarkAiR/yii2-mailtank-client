<?php
/**
 * Class MailtankBaseLayout
 */
class MailtankBaseLayout extends MailtankRecord
{
    const ENDPOINT = '/base_layouts/';

    public $name;
    public $markup;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function rules()
    {
        return array(
            array('markup', 'safe'),
            array('name', 'length', 'max' => 60),
            array('name, markup', 'required'),
            array('id', 'safe'),
        );
    }

    /**
     * Returns the list of attribute names of the model.
     * @return array list of attribute names.
     */
    public function attributeNames()
    {
        return array_merge_recursive(parent::attributeNames(), array(
            'markup',
            'name',
        ));
    }

    /**
     * Delete base layout
     */
    public function delete()
    {
        $this->url = self::ENDPOINT.$this->id;
        $this->setIsNewRecord(false);
        var_dump($this->url);
        return parent::delete();
    }
}