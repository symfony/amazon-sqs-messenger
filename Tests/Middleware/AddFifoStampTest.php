<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Messenger\Bridge\AmazonSqs\Tests\Middleware;

use Symfony\Component\Messenger\Bridge\AmazonSqs\Middleware\AddFifoStamp;
use Symfony\Component\Messenger\Bridge\AmazonSqs\Middleware\WithMessageDeduplicationId;
use Symfony\Component\Messenger\Bridge\AmazonSqs\Middleware\WithMessageGroupId;
use Symfony\Component\Messenger\Bridge\AmazonSqs\Transport\AmazonSqsFifoStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Test\Middleware\MiddlewareTestCase;

class AddFifoStampTest extends MiddlewareTestCase
{
    public function testAddStampWithGroupIdOnly(): void
    {
        $middleware = new AddFifoStamp();
        $envelope = new Envelope(new WithMessageGroupIdMessage('groupId'));
        $finalEnvelope = $middleware->handle($envelope, $this->getStackMock());
        $stamp = $finalEnvelope->last(AmazonSqsFifoStamp::class);
        $this->assertNotNull($stamp);
        /** @var AmazonSqsFifoStamp $stamp */
        $this->assertEquals('groupId', $stamp->getMessageGroupId());
        $this->assertNull($stamp->getMessageDeduplicationId());
    }

    public function testHandleWithDeduplicationIdOnly(): void
    {
        $middleware = new AddFifoStamp();
        $envelope = new Envelope(new WithMessageDeduplicationIdMessage('deduplicationId'));
        $finalEnvelope = $middleware->handle($envelope, $this->getStackMock());
        $stamp = $finalEnvelope->last(AmazonSqsFifoStamp::class);
        $this->assertNotNull($stamp);
        /** @var AmazonSqsFifoStamp $stamp */
        $this->assertEquals('deduplicationId', $stamp->getMessageDeduplicationId());
        $this->assertNull($stamp->getMessageGroupId());
    }

    public function testHandleWithGroupIdAndDeduplicationId(): void
    {
        $middleware = new AddFifoStamp();
        $envelope = new Envelope(new WithMessageDeduplicationIdAndMessageGroupIdMessage('my_group', 'my_random_id'));
        $finalEnvelope = $middleware->handle($envelope, $this->getStackMock());
        $stamp = $finalEnvelope->last(AmazonSqsFifoStamp::class);
        $this->assertNotNull($stamp);
        /** @var AmazonSqsFifoStamp $stamp */
        $this->assertEquals('my_random_id', $stamp->getMessageDeduplicationId());
        $this->assertEquals('my_group', $stamp->getMessageGroupId());
    }

    public function testHandleWithoutId(): void
    {
        $middleware = new AddFifoStamp();
        $envelope = new Envelope(new WithoutIdMessage());
        $finalEnvelope = $middleware->handle($envelope, $this->getStackMock());
        $stamp = $finalEnvelope->last(AmazonSqsFifoStamp::class);
        /** @var AmazonSqsFifoStamp $stamp */
        $this->assertNull($stamp);
    }
}

class WithMessageDeduplicationIdAndMessageGroupIdMessage implements WithMessageDeduplicationId, WithMessageGroupId
{
    private string $messageGroupId;
    private string $messageDeduplicationId;

    public function __construct(
        string $messageGroupId,
        string $messageDeduplicationId
    )
    {
        $this->messageGroupId = $messageGroupId;
        $this->messageDeduplicationId = $messageDeduplicationId;
    }

    public function messageDeduplicationId(): string
    {
        return $this->messageDeduplicationId;
    }

    public function messageGroupId(): string
    {
        return $this->messageGroupId;
    }
}


class WithMessageDeduplicationIdMessage implements WithMessageDeduplicationId
{
    private string $messageDeduplicationId;

    public function __construct(
        string $messageDeduplicationId
    )
    {
        $this->messageDeduplicationId = $messageDeduplicationId;
    }

    public function messageDeduplicationId(): string
    {
        return $this->messageDeduplicationId;
    }
}


class WithMessageGroupIdMessage implements WithMessageGroupId
{
    private string $messageGroupId;

    public function __construct(
        string $messageGroupId
    )
    {
        $this->messageGroupId = $messageGroupId;
    }

    public function messageGroupId(): string
    {
        return $this->messageGroupId;
    }
}
class WithoutIdMessage
{
}
