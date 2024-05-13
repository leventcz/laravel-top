<?php

declare(strict_types=1);

namespace Leventcz\Top\Extensions;

use Symfony\Component\Console\Output\BufferedOutput as BaseBufferedOutput;

class BufferedOutput extends BaseBufferedOutput
{
    public function write($messages, $newline = false, $options = self::OUTPUT_NORMAL): void
    {
        parent::write($messages, $newline, self::OUTPUT_RAW);
    }
}
