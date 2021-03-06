<?php

namespace BrowscapTest\Generator;

use Browscap\Generator\DataCollection;
class DataCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function getPlatformsJsonFixture()
    {
        return __DIR__ . '/../../fixtures/platforms/platforms.json';
    }

    private function getUserAgentFixtures()
    {
        $dir = __DIR__ . '/../../fixtures/ua';

        return [
            $dir . '/default-properties.json',
            $dir . '/test1.json',
            $dir . '/test2.json',
            $dir . '/test3.json',
            $dir . '/default-browser.json',
        ];
    }

    public function testAddPlatformsFile()
    {
        $data = new DataCollection('1234');

        $data->addPlatformsFile($this->getPlatformsJsonFixture());

        $platforms = $data->getPlatforms();

        $expected = [
            'Platform1' => [
                'match' => '*Platform1*',
                'properties' => [
                    'Platform' => 'Platform1',
                    'Platform_Description' => 'The first test platform',
                    'Win32' => 'false',
                ],
            ],
            'Platform2' => [
                'match' => '*Platform2*',
                'properties' => [
                    'Platform' => 'Platform2',
                    'Win32' => 'false',
                ],
            ],
        ];

        $this->assertSame($expected, $platforms);

        $this->assertSame($expected['Platform1'], $data->getPlatform('Platform1'));
        $this->assertSame($expected['Platform2'], $data->getPlatform('Platform2'));
    }

    public function testAddPlatformsFileThrowsExceptionIfFileDoesNotExist()
    {
        $data = new DataCollection('1234');

        $file = '/hopefully/this/file/does/not/exist';

        $this->setExpectedException('\RuntimeException', 'File "' . $file . '" does not exist');
        $data->addPlatformsFile($file);
    }

    public function testAddPlatformsFileThrowsExceptionIfFileContainsInvalidJson()
    {
        $tmpfile = tempnam(sys_get_temp_dir(), 'browscaptest');

        $in = <<<HERE
this is not valid JSON
HERE;

        file_put_contents($tmpfile, $in);

        $data = new DataCollection('1234');

        $this->setExpectedException('\RuntimeException', 'File "' . $tmpfile . '" had invalid JSON.');
        $data->addPlatformsFile($tmpfile);

        unlink($tmpfile);
    }

    public function testGetPlatformThrowsExceptionIfPlatformDoesNotExist()
    {
        $data = new DataCollection('1234');

        $data->addPlatformsFile($this->getPlatformsJsonFixture());

        $this->setExpectedException('\OutOfBoundsException', 'Platform "NotExists" does not exist in data');
        $data->getPlatform('NotExists');
    }

    public function testGetVersion()
    {
        $data = new DataCollection('1234');
        $this->assertSame('1234', $data->getVersion());
    }

    public function testGetGenerationDate()
    {
        $data = new DataCollection('1234');

        // Time isn't always exact, so allow a few seconds grace either way...
        $currentTime = time();
        $minTime = $currentTime - 3;
        $maxTime = $currentTime + 3;

        $testDateTime = $data->getGenerationDate();

        $this->assertInstanceOf('\DateTime', $testDateTime);

        $testTime = $testDateTime->getTimestamp();
        $this->assertGreaterThanOrEqual($minTime, $testTime);
        $this->assertLessThanOrEqual($maxTime, $testTime);
    }

    public function testAddSourceFile()
    {
        $data = new DataCollection('1234');

        $files = $this->getUserAgentFixtures();
        foreach ($files as $file)
        {
            $data->addSourceFile($file);
        }

        $divisions = $data->getDivisions();

        $expected = require_once __DIR__ . '/../../fixtures/DataCollectionTestArray.php';

        $this->assertEquals($expected, $divisions);
    }

    public function testAddSourceFileThrowsExceptionIfFileDoesNotExist()
    {
        $data = new DataCollection('1234');

        $file = '/hopefully/this/file/does/not/exist';

        $this->setExpectedException('\RuntimeException', 'File "' . $file . '" does not exist');
        $data->addSourceFile($file);
    }

    public function testAddSourceFileThrowsExceptionIfFileContainsInvalidJson()
    {
        $tmpfile = tempnam(sys_get_temp_dir(), 'browscaptest');

        $in = <<<HERE
this is not valid JSON
HERE;

        file_put_contents($tmpfile, $in);

        $data = new DataCollection('1234');

        $this->setExpectedException('\RuntimeException', 'File "' . $tmpfile . '" had invalid JSON.');
        $data->addSourceFile($tmpfile);

        unlink($tmpfile);
    }
}
