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

        if (!$source = $this->env->get('archive:source')) {
            $this->logError('Missing archive:source environment variable.');
            return false;
        }

        if (!$target = $this->env->get('archive:target')) {
            $this->logError('Missing archive:target environment variable.');
            return false;
        }

        $zip = new \ZipArchive();
        if ($zip->open($this->buildPath($target), \ZipArchive::CREATE) !== true) {
            $this->logError('Failed to create ' . $target);
            return false;
        }

        $items = $finder->in($this->buildPath($source));
        $exclude = $this->env->get('archive:blacklist', []);

        foreach ($items as $item) {
            if (in_array($item->getRelativePathname(), $exclude)) {
                continue;
            }

            if ($item->isDir()) {
                $zip->addEmptyDir($item->getRelativePathname());
            } else {
                $zip->addFile($item->getRelativePathname());
            }
        }

        $zip->close();
        $this->logMessage('Created ' . $target, true);

        return true;
    }
}