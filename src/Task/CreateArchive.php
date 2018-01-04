<?php

namespace Tapegun\Task;

use Symfony\Component\Finder\Finder;
use Tapegun\AbstractTask;

class CreateArchive extends AbstractTask
{
    protected $description = 'Creating ZIP archive';

    /**
     * @inheritdoc
     */
    public function run()
    {
        $finder = new Finder();

        if (!$source = $this->env->resolve('{{archive:source}}')) {
            $this->logError('Missing archive:source environment variable.');
            return false;
        }

        if (!$target = $this->env->resolve('{{archive:target}}')) {
            $this->logError('Missing archive:target environment variable.');
            return false;
        }

        $zip = new \ZipArchive();
        if ($zip->open($this->buildPath($target), \ZipArchive::CREATE) !== true) {
            $this->logError('Failed to create ' . $target);
            return false;
        }

        $items = $finder->in($this->buildPath($source))->ignoreDotFiles(false);
        $exclude = $this->env->get('archive:blacklist', []);

        foreach ($items as $item) {
            foreach ($exclude as $path) {
                if ($path[strlen($path) - 1] === '/') {
                    // Test for descendant of directory
                    if (strpos($item->getRelativePathname(), $path) === 0) {
                        continue 2;
                    }
                } else {
                    // Test for exact match
                    if ($item->getRelativePathname() === $path) {
                        continue 2;
                    }
                }
            }

            if ($item->isDir()) {
                $zip->addEmptyDir($item->getRelativePathname());
            } else {
                $zip->addFile($item->getPathname(), $item->getRelativePathname());
            }
        }

        if ($zip->close()) {
            $this->logMessage('Created ' . $target, true);
            return true;
        } else {
            $this->logError('Failed to create ' . $target);
            return false;
        }
    }
}