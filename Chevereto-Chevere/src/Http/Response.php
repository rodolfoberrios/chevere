<?php

declare(strict_types=1);

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevere\Http;

use Chevere\Contracts\Http\ResponseContract;
use Chevere\JsonApi\JsonApi;
use DateTime;
use DateTimeZone;
use Exception;
use GuzzleHttp\Psr7\Response as GuzzleHttpResponse;

use function GuzzleHttp\Psr7\stream_for;

use const Chevere\CLI;

// final class Response extends GuzzleHttpResponse implements ResponseContract
final class Response implements ResponseContract
{

    /** @var GuzzleHttpResponse */
    private $guzzle;

    /** @var string */
    private $status;

    /** @var string */
    private $headers;

    /** @var string */
    private $content;

    public function __construct()
    {
        $this->guzzle = new GuzzleHttpResponse(200, $this->getDateHeader());
        $this->guzzle = $this->guzzle->withAddedHeader('WWW-Authenticate', 'Negotiate');
        $this->guzzle = $this->guzzle->withAddedHeader('WWW-Authenticate', 'NTLM');
    }

    public function guzzle(): GuzzleHttpResponse
    {
        return $this->guzzle;
    }

    /**
     * {@inheritdoc}
     */
    public function status(): string
    {
        if (!isset($this->status)) {
            $this->setStatus();
        }
        return $this->status ?? '';
    }

    public function headers(): string
    {
        if (!isset($this->headers)) {
            $this->setHeaders();
        }
        return $this->headers ?? '';
    }

    public function content(): string
    {
        if (!isset($this->content)) {
            $this->setContent();
        }
        return $this->content ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function withJsonApi(JsonApi $jsonApi): ResponseContract
    {
        $body = stream_for($jsonApi->toString());
        $new = clone $this;
        $new = $new->withJsonApiHeaders();
        $new->guzzle = $new->guzzle
            ->withBody($body);
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withJsonApiHeaders(): ResponseContract
    {
        $new = clone $this;
        $new->guzzle = $new->guzzle->withHeader('Content-Type', 'application/vnd.api+json');
        return $new;
    }

    public function sendHeaders(): ResponseContract
    {
        header($this->status(), true, $this->guzzle->getStatusCode());
        foreach ($this->guzzle->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }
        return $this;
    }

    public function sendBody(): ResponseContract
    {
        $stream = $this->guzzle->getBody();
        if ($stream->isSeekable()) {
            $stream->rewind();
        }
        while (!$stream->eof()) {
            echo $stream->read(1024 * 8);
        }
        return $this;
    }

    private function setStatus(): void
    {
        $this->status = sprintf('HTTP/%s %s %s', $this->guzzle->getProtocolVersion(), $this->guzzle->getStatusCode(), $this->guzzle->getReasonPhrase());
    }

    private function setHeaders(): void
    {
        $headers = [];
        foreach ($this->guzzle->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $headers[] = "$name: $value";
            }
        }
        $this->headers = implode("\n", $headers);
    }

    private function setContent(): void
    {
        $this->content = (string) $this->guzzle->getBody();
    }

    private function getDateHeader(): array
    {
        $date = new DateTime('now', new DateTimeZone('UTC'));
        return ['Date' => $date->format('D, d M Y H:i:s') . ' GMT'];
    }
}