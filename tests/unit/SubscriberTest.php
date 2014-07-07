<?php

namespace mailtank\tests\unit;

use \mailtank\MailtankException;
use \mailtank\models\MailtankSubscriber;

class SubscriberTest extends \PHPUnit_Framework_TestCase
{
    private static $subscriberIds = [];

    public static function createBasicModel()
    {
        $model = new MailtankSubscriber();
        $id = uniqid();
        $model->setAttributes([
            'id'    => $id,
            'email' => $id . "@example.com",
        ]);

        return $model;
    }

    private function clearUnusedData()
    {
        foreach (self::$subscriberIds as $subscriberId) {
            $subscriber = MailtankSubscriber::findByPk($subscriberId);
            $this->assertTrue($subscriber->delete());
        }
        self::$subscriberIds = [];
    }

    public function testCreate()
    {
        $subscriber = self::createBasicModel();
        $subscriber->tags = ['test1', 'test2'];
        $subscriber->setProperties([
            'property1' => 1,
            'property2' => 0,
            'property3' => 3,
        ]);
        $subscriber->setProperty('property2', 2);

        $unsavedModel = clone $subscriber;
        $res = $subscriber->save();
        if (!$res) {
            print_r($subscriber->getErrors());
            $this->assertTrue(false);
        }
        self::$subscriberIds[] = $subscriber->id;

        $this->assertEquals($subscriber->id, $unsavedModel->id);
        $this->assertEquals($subscriber->email, $unsavedModel->email);

        $this->assertContains(
            [
                'property1' => 1,
                'property2' => 2,
                'property3' => 3,
            ],
            $subscriber->attributes
        );

        $this->assertContains(
            [
                'test1',
                'test2',
            ],
            $subscriber->attributes
        );

        $this->assertNotNull($subscriber->id);
        $this->clearUnusedData();
    }

    public function testGetById()
    {
        $savedModel = self::createBasicModel();
        $res = $savedModel->save();
        if (!$res) {
            print_r($savedModel->getErrors());
            $this->assertTrue(false);
        }
        self::$subscriberIds[] = $savedModel->id;

        $subscriber = MailtankSubscriber::findByPk($savedModel->id);
        $this->assertEquals($savedModel->attributes, $subscriber->attributes);
        $this->clearUnusedData();
    }

    public function testUpdate()
    {
        $savedModel = self::createBasicModel();
        $savedModel->tags = ['test1', 'test2'];
        $savedModel->setProperties([
            'property1' => 1,
            'property2' => 0,
            'property3' => 3,
        ]);
        $savedModel->setProperty('property2', 2);
        $res = $savedModel->save();
        if (!$res) {
            print_r($savedModel->getErrors());
            $this->assertTrue(false);
        }
        self::$subscriberIds[] = $savedModel->id;

        $model = clone $savedModel;

        $newEmail = uniqid() . '@example.com';

        $model->setProperty('property2', 2);
        $model->setProperty('property4', 4);
        $model->tags = ['test2', 'test3'];
        $model->email = $newEmail;

        $res = $model->save();
        if (!$res) {
            print_r($model->getErrors());
            $this->assertTrue(false);
        }

        foreach ([$model, $model::findByPk($model->id)] as $_model) {
            $this->assertEquals($model->id, $_model->id);
            $this->assertEquals($newEmail, $_model->email);

            $this->assertContains(
                [
                    'property1' => 1,
                    'property2' => 2,
                    'property3' => 3,
                    'property4' => 4,
                ],
                $_model->attributes
            );

            $this->assertContains(
                [
                    'test2',
                    'test3',
                ],
                $_model->attributes
            );
        }
        $this->clearUnusedData();
    }

    public function testDelete()
    {
        $model = self::createBasicModel();
        $res = $model->save();
        if (!$res) {
            print_r($model->getErrors());
            $this->assertTrue(false);
        }

        // dont need self::$subscriberIds[] = $model->id;

        $this->assertTrue($model->delete());
        $this->assertFalse(MailtankSubscriber::findByPk($model->id));
        $this->clearUnusedData();
    }

    public function testRefresh()
    {
        $savedModel = self::createBasicModel();

        $e = false;
        try {
            $savedModel->refresh();
        } catch (MailtankException $e) {
            $e = true;
        }
        $this->assertTrue($e, 'Updated model cant be refreshed');
        $res = $savedModel->save();
        if (!$res) {
            print_r($savedModel->getErrors());
            $this->assertTrue(false);
        }
        self::$subscriberIds[] = $savedModel->id;
        $this->assertTrue($savedModel->refresh());
        $this->clearUnusedData();
    }

    public function testPatchTags()
    {
        $subscribers = [];
        $subscribers_id = [];
        for ($i = 0; $i < 2; $i++) {
            $subscriber = self::createBasicModel();
            $res = $subscriber->save();
            if (!$res) {
                print_r($subscriber->getErrors());
                $this->assertTrue(false);
            }
            self::$subscriberIds[] = $subscriber->id;
            $subscribers[] = $subscriber;
            $subscribers_id[] = $subscriber->id;
        }

        $tag = 'test_tag_' . uniqid();
        $result = MailtankSubscriber::patchTags($subscribers_id, $tag);
        $this->assertTrue($result);

        foreach ($subscribers as $subscriber) {
            $this->assertTrue($subscriber->refresh());
            $this->assertContains($tag, $subscriber->tags);
        }
        $this->clearUnusedData();
    }

    public function testPatchTagsAll()
    {
        $subscribers = [];
        for ($i = 0; $i < 2; $i++) {
            $subscriber = self::createBasicModel();
            $res = $subscriber->save();
            if (!$res) {
                print_r($subscriber->getErrors());
                $this->assertTrue(false);
            }
            self::$subscriberIds[] = $subscriber->id;
            $subscribers[] = $subscriber;
        }

        $tag = 'test_tag_' . uniqid();
        $result = MailtankSubscriber::patchTags('all', $tag);
        $this->assertTrue($result);

        foreach ($subscribers as $subscriber) {
            $this->assertTrue($subscriber->refresh());
            $this->assertContains($tag, $subscriber->tags);
        }
        $this->clearUnusedData();
    }

    public function testEmailAsId()
    {
        $subscriber = self::createBasicModel();
        $subscriber->id = $subscriber->email;
        $res = $subscriber->save();
        if (!$res) {
            print_r($subscriber->getErrors());
            $this->assertTrue(false);
        }
        $this->assertTrue($subscriber->refresh());
        $this->assertTrue($subscriber->delete());
        $this->assertFalse($subscriber->refresh());
    }
}