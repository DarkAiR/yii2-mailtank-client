<?php

namespace mailtank\tests\unit;

use \mailtank\MailtankException;
use \mailtank\models\MailtankBaseLayout;

class BaseLayoutTest extends \PHPUnit_Framework_TestCase
{
    private static $layoutId = false;

    public static function createBasicModel()
    {
        $model = new MailtankBaseLayout();
        $id = uniqid();
        $model->setAttributes([
            'id'        => $id,
            'name'      => 'test BaseLayoutTest '.$id,
            'markup'    => 'Hello, {{username}}! {{unsubscribe_link}}',
        ]);
        return $model;
    }

    private function clearUnusedData()
    {
        if (self::$layoutId !== false) {
            $layout = new MailtankBaseLayout();
            $layout->id = self::$layoutId;
            $this->assertTrue($layout->delete());
            self::$layoutId = false;
        }
    }

    public function testCreate()
    {
        $layout = self::createBasicModel();
        $unsavedModel = clone $layout;

        $res = $layout->save();
        if (!$res) {
            print_r($layout->getErrors());
            $this->assertTrue(false);
        }
        self::$layoutId = $layout->id;

        $this->assertEquals($unsavedModel->id, $layout->id);
        $this->assertEquals('test BaseLayoutTest '.$layout->id, $layout->name);
        $this->assertEquals('Hello, {{username}}! {{unsubscribe_link}}', $layout->markup);

        $this->clearUnusedData();
    }

    public function testGetById()
    {
        $layout = self::createBasicModel();
        $res = $layout->save();
        if (!$res) {
            print_r($layout->getErrors());
            $this->assertTrue(false);
        }
        self::$layoutId = $layout->id;

        try {
            MailtankBaseLayout::findByPk($layout->id);
        } catch (MailtankException $e) {
            $this->clearUnusedData();
            return;
        }
        $this->fail('BaseLayout cant be retrieved by id');
        $this->clearUnusedData();
    }

    public function testUpdate()
    {
        $layout = self::createBasicModel();
        $res = $layout->save();
        if (!$res) {
            print_r($layout->getErrors());
            $this->assertTrue(false);
        }
        self::$layoutId = $layout->id;

        try {
            $layout->save();
        } catch (MailtankException $e) {
            $this->clearUnusedData();
            return;
        }
        $this->fail('BaseLayout cant be saved');
        $this->clearUnusedData();
    }

    public function testDelete()
    {
        $layout = self::createBasicModel();
        $res = $layout->save();
        if (!$res) {
            print_r($layout->getErrors());
            $this->assertTrue(false);
        }
        // dont need self::$layoutId = $layout->id;

        try {
            $layout->delete();
        } catch (MailtankException $e) {
            $this->fail('BaseLayout cant be deleted');
            $this->clearUnusedData();
            return;
        }
        $this->clearUnusedData();
    }

    public function testRefresh()
    {
        $layout = self::createBasicModel();
        $res = $layout->save();
        if (!$res) {
            print_r($layout->getErrors());
            $this->assertTrue(false);
        }
        self::$layoutId = $layout->id;

        try {
            $layout->refresh();
        } catch (MailtankException $e) {
            $this->clearUnusedData();
            return;
        }

        $this->fail('BaseLayout cant be refreshed');
        $this->clearUnusedData();
    }
}