<?php

namespace mailtank\tests\unit;

use \mailtank\MailtankException;
use \mailtank\models\MailtankLayout;

class LayoutTest extends \PHPUnit_Framework_TestCase
{
    private static $layoutId = false;

    public static function createBasicModel()
    {
        $model = new MailtankLayout();
        $id = uniqid();
        $model->setAttributes([
            'id'                => $id,
            'name'              => 'test Layout '.$id,
            'markup'            => 'Hello, {{username}}! {{unsubscribe_link}}',
            'subject_markup'    => 'Hello, {{username}}!',
        ]);

        return $model;
    }

    private function clearUnusedData()
    {
        if (self::$layoutId !== false) {
            $layout = new MailtankLayout();
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
        $this->assertEquals('test Layout '.$layout->id, $layout->name);
        $this->assertEquals('Hello, {{username}}! {{unsubscribe_link}}', $layout->markup);
        $this->assertEquals('Hello, {{username}}!', $layout->subject_markup);

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
            MailtankLayout::findByPk($layout->id);
        } catch (MailtankException $e) {
            $this->clearUnusedData();
            return;
        }
        $this->fail('Layout cant be retrieved by id');
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
        $this->fail('Layout cant be saved');
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
            $this->clearUnusedData();
            $this->fail('Layout cant be deleted');
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

        $this->fail('Layout cant be refreshed');
        $this->clearUnusedData();
    }
}
