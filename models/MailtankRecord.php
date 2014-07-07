<?php

namespace mailtank\models;

use Yii;
use yii\base\ModelEvent;
use mailtank\MailtankException;

/**
 * Class MailtankRecord
 */
abstract class MailtankRecord extends \yii\base\Model
{
    public $id;

    protected $url;
    protected $isNewRecord = true;
    protected $crud = [
        'create' => true,
        'read'   => true,
        'update' => true,
        'delete' => true
    ];

    /**
     * Returns the list of attribute names of the model.
     * @return array list of attribute names.
     */
    public function attributes()
    {
        return ['id'];
    }

    /**
     * Saves the current record.
     *
     * @param boolean $runValidation whether to perform validation before saving the record.
     * If the validation fails, the record will not be saved to database.
     * @param array $attributeNames list of attribute names that need to be saved. Defaults to null,
     * meaning all attributes that are loaded from DB will be saved.
     * @return boolean whether the saving succeeds
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->getIsNewRecord())
            return $this->insert($runValidation, $attributeNames);

        return $this->update($runValidation, $attributeNames) !== false;
    }

    /**
     * Insert
     */
    private function insert($runValidation = true, $attributeNames = null)
    {
        if ($runValidation && !$this->validate($attributeNames)) {
            Yii::info('Model not inserted due to validation error.', __METHOD__);
            return false;
        }
    
        if (!$this->getIsNewRecord())
            throw new MailtankException('The mailtank record cannot be created to API because it is not new.');

        if (!$this->crud['create'])
            throw new MailtankException('This mailtank record does not supports create method.');

        $fields = $this->getAttributes($attributeNames);
        $fields = $this->beforeSendAttributes($fields);
        try {
            $data = Yii::$app->mailtankClient->sendRequest(
                $this::ENDPOINT,
                json_encode($fields),
                'post'
            );
        } catch (MailtankException $e) {
            if ($e->validationErrors) {
                foreach ($e->validationErrors as $errors) {
                    foreach ($errors as $err) {
                        $this->addError($err);
                    }
                }
                return false;
            } else {
                throw $e;
            }
        }
        if (empty($data['id']))
            throw new MailtankException('Endpoint ' . $this::ENDPOINT . ' returned no id on insert');

        $this->setAttributes($data, false);
        $this->setIsNewRecord(false);
        return true;
    }

    /**
     * Update
     * @param null $attributes
     * @throws MailtankException
     * @return bool
     */
    private function update($runValidation = true, $attributeNames = null)
    {
        if ($runValidation && !$this->validate($attributeNames)) {
            Yii::info('Model not updated due to validation error.', __METHOD__);
            return false;
        }

        if ($this->getIsNewRecord())
            throw new MailtankException(\Yii::t('yii', 'The active record cannot be updated because it is new.'));
        
        if (!$this->crud['update'])
            throw new MailtankException('This mailtank model does not supports update method.');

        $fields = $this->getAttributes($attributeNames);
        $fields = $this->beforeSendAttributes($fields);
        try {
            $data = Yii::$app->mailtankClient->sendRequest(
                $this->url,
                json_encode($fields),
                'put'
            );
        } catch (MailtankException $e) {
            if ($e->validationErrors) {
                foreach ($e->validationErrors as $errors) {
                    foreach ($errors as $err) {
                        $this->addError($err);
                    }
                }
                return false;
            } else {
                throw $e;
            }
        }

        if (!empty($data['message']))
            return false;

        $this->setAttributes($data, false);
        return true;
    }

    /**
     * @param string $pk (external_id || id)
     * @return bool|MailtankRecord
     * @throws MailtankException
     */
    public static function findByPk($pk)
    {
        $className = get_called_class();
        $model = new $className;

        if (!$model->crud['read'])
            throw new MailtankException('<{$className}> does not supports find method.');

        try {
            $data = Yii::$app->mailtankClient->sendRequest(
                $model::ENDPOINT . $pk,
                null,
                'get'
            );
        } catch (MailtankException $e) {
            if ($e->getCode() == 404)
                return false;
            throw $e;
        }

        if ($data) {
            if (!empty($data['message']))
                return false;
            $model->setAttributes($data, false);
            $model->setIsNewRecord(false);
            return $model;
        }

        return false;
    }

    /**
     * @throws MailtankException
     * @return bool
     */
    public function delete()
    {
        if (!$this->crud['delete'])
            throw new MailtankException('This mailtank model doesnt support delete method.');

        Yii::$app->mailtankClient->sendRequest(
            $this->url,
            null,
            'delete'
        );
        return true;
    }

    /**
     * Refresh
     */
    public function refresh()
    {
        if (!$this->crud['read'])
            throw new MailtankException('This mailtank model does not supports refresh method.');

        if (empty($this->url) || $this->getIsNewRecord())
            throw new MailtankException('This model is new and cant be refreshed.');

        try {
            $data = Yii::$app->mailtankClient->sendRequest(
                $this->url,
                null,
                'get'
            );
        } catch (MailtankException $e) {
            if ($e->getCode() == 404)
                return false;
            throw $e;
        }

        if ($data) {
            if (!empty($data['message']))
                return false;
            $this->setAttributes($data, false);
            return true;
        }

        return false;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function beforeSendAttributes($fields)
    {
        $fieldsTmp = $fields;
        $fields = [];
        if ($fieldsTmp) {
            foreach ($fieldsTmp as $key => $value) {
                if ($value !== null)
                    $fields[$key] = $value;
            }
        }
        return $fields;
    }

    /**
     * @param bool $isNewRecord
     */
    protected function setIsNewRecord($isNewRecord)
    {
        $this->isNewRecord = $isNewRecord;
    }

    /**
     * @return bool
     */
    public function getIsNewRecord()
    {
        return $this->isNewRecord;
    }
}