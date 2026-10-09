<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Messenger\Bridge\AmazonSqs\Transport;

use Symfony\Component\Messenger\Stamp\SenderStampInterface;

final class AmazonSqsFairQueueStamp implements SenderStampInterface
{
    public function __construct(
        private string $messageGroupId,
    ) {
    }

    public function getMessageGroupId(): string
    {
        return $this->messageGroupId;
    }
}
