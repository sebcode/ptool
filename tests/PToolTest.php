<?php

use PHPUnit_Framework_TestCase as TestCase;

class PToolTest extends TestCase
{
    protected $ptool;

    public function setUp()
    {
        $this->ptool = new PTool\PTool(__DIR__ . '/testdata/');
    }

    public function testGetProjects()
    {
        $projects = $this->ptool->getProjects();
        $this->assertEquals(3, count($projects));
    }

    public function testGetRepoPath()
    {
        $basePath = $this->ptool->getBasePath();
        $project = $this->ptool->getProject('slv');
        $this->assertEquals($basePath . 'silver/git/', $project->getRepoPath());

        $project = $this->ptool->getProject('stg');
        $this->assertEquals($basePath . 'dev-STG/', $project->getRepoPath());
    }

}
