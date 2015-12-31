<?php

class PublishableSiteTreeTest extends SapphireTest
{

    protected $requiredExtensions = array(
        'PublishableSiteTreeTest_Publishable' => array('PublishableSiteTree')
    );

    public function setUp()
    {
        parent::setUp();
        Config::inst()->nest();
        Config::inst()->update('StaticPagesQueue', 'realtime', true);
    }

    public function tearDown()
    {
        Config::inst()->unnest();
        parent::tearDown();
    }

    public function testObjectsToUpdateOnPublish()
    {
        $parent = Object::create('PublishableSiteTreeTest_Publishable');
        $parent->Title = 'parent';

        $stub = $this->getMock(
            'PublishableSiteTreeTest_Publishable',
            array('getParentID', 'Parent')
        );
        $stub->Title = 'stub';

        $stub->expects($this->once())
            ->method('getParentID')
            ->will($this->returnValue('2'));

        $stub->expects($this->once())
            ->method('Parent')
            ->will($this->returnValue($parent));

        $objects = $stub->objectsToUpdate(array('action' => 'publish'));
        $this->assertEquals($objects->column('Title'), array('stub', 'parent'), 'Updates itself and parent on publish');
    }

    public function testObjectsToUpdateOnUnpublish()
    {
        $parent = Object::create('PublishableSiteTreeTest_Publishable');
        $parent->Title = 'parent';

        $stub = $this->getMock(
            'PublishableSiteTreeTest_Publishable',
            array('getParentID', 'Parent')
        );
        $stub->Title = 'stub';

        $stub->expects($this->once())
            ->method('getParentID')
            ->will($this->returnValue('2'));

        $stub->expects($this->once())
            ->method('Parent')
            ->will($this->returnValue($parent));

        $objects = $stub->objectsToUpdate(array('action' => 'unpublish'));
        $this->assertEquals($objects->column('Title'), array('parent'), 'Updates parent on unpublish');
    }

    public function testObjectsToDeleteOnPublish()
    {
        $stub = Object::create('PublishableSiteTreeTest_Publishable');
        $objects = $stub->objectsToDelete(array('action' => 'publish'));
        $this->assertEquals($objects->column('Title'), array(), 'Deletes nothing on publish');
    }

    public function testObjectsToDeleteOnUnpublish()
    {
        $stub = Object::create('PublishableSiteTreeTest_Publishable');
        $stub->Title = 'stub';
        $objects = $stub->objectsToDelete(array('action' => 'unpublish'));
        $this->assertEquals($objects->column('Title'), array('stub'), 'Deletes itself on unpublish');
    }

    public function testObjectsToUpdateOnPublishIfVirtualExists()
    {
        $redir = Object::create('PublishableSiteTreeTest_Publishable');
        $redir->Title = 'virtual';

        $stub = $this->getMock(
            'PublishableSiteTreeTest_Publishable',
            array('getMyVirtualPages')
        );
        $stub->Title = 'stub';

        $stub->expects($this->once())
            ->method('getMyVirtualPages')
            ->will($this->returnValue(
                new ArrayList(array($redir))
            ));

        $objects = $stub->objectsToUpdate(array('action' => 'publish'));
        $this->assertTrue(in_array('virtual', $objects->column('Title'), 'Updates related virtual page'));
    }

    public function testObjectsToDeleteOnUnpublishIfVirtualExists()
    {
        $redir = Object::create('PublishableSiteTreeTest_Publishable');
        $redir->Title = 'virtual';

        $stub = $this->getMock(
            'PublishableSiteTreeTest_Publishable',
            array('getMyVirtualPages')
        );
        $stub->Title = 'stub';

        $stub->expects($this->once())
            ->method('getMyVirtualPages')
            ->will($this->returnValue(
                new ArrayList(array($redir))
            ));

        $objects = $stub->objectsToDelete(array('action' => 'unpublish'));
        $this->assertTrue(in_array('virtual', $objects->column('Title'), 'Deletes related virtual page'));
    }
}

class PublishableSiteTreeTest_Publishable extends SiteTree implements TestOnly
{
}
