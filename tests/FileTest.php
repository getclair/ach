<?php

namespace Clair\Ach\Tests;

use Clair\Ach\Parser;
use Illuminate\Support\Facades\File;

class FileTest extends TestCase
{
    public function test_generate_ach_file()
    {
        foreach (File::files(__DIR__.'/testdata') as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $name = $file->getFilename();

            $parser = new Parser($contents = $file->getContents());

            $file = $parser->parse();

            $output = $file->generateFile();

            $this->assertEquals($output, $contents, $name);
        }
    }

    public function test_get_file_data()
    {
        $contents = File::get(__DIR__.'/testdata/ppd-credit.ach');

        $parser = new Parser($contents);

        $file = $parser->parse();

        $this->assertIsArray($file->data());
    }
}
