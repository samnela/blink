<?php

namespace blink\tests\http;

use blink\http\Stream;
use blink\http\Uri;
use blink\http\HeaderBag;
use blink\http\ParamBag;
use blink\http\Request;
use blink\tests\TestCase;
use Psr\Http\Message\UriInterface;

class RequestTest extends TestCase
{
    public function testDefault()
    {
        $request = new Request([]);

        $this->assertInstanceOf(ParamBag::class, $request->params);
        $this->assertInstanceOf(HeaderBag::class, $request->headers);
        $this->assertInstanceOf(ParamBag::class, $request->payload);

        $this->assertEquals('', $request->method());
        $this->assertEquals('', $request->getMethod());

        $uri = $request->getUri();
        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertInstanceOf(Uri::class, $uri);

        $this->assertEmpty($uri->getScheme());
        $this->assertEmpty($uri->getUserInfo());
        $this->assertEmpty($uri->getHost());
        $this->assertNull($uri->getPort());
        $this->assertEmpty($uri->getPath());
        $this->assertEmpty($uri->getQuery());
        $this->assertEmpty($uri->getFragment());

        $this->assertEquals('', $request->host());
        $this->assertEquals('', $request->url());
    }

    public function testBasic()
    {
        $body = new Stream('php://memory', 'w+');
        $body->write(json_encode(['foo' => 'bar']));

        $request = new Request([
            'method' => 'POST',
            'uri' => new Uri('', ['query' => 'a=b&b=c&r.n=a&r-n=a', 'scheme' => 'https']),
            'body' => $body,
            'headers' => [
                'Content-Type' => 'application/json; Charset=utf8',
            ]
        ]);

        $this->assertTrue($request->is('post'));
        $this->assertEquals(['a' => 'b', 'b' => 'c', 'r.n' => 'a', 'r-n' => 'a'], $request->params->all());
        $this->assertEquals(['foo' => 'bar'], $request->payload->all());

        $this->assertEquals('b', $request->input('a'));
        $this->assertEquals(true, $request->has('foo'));
        $this->assertEquals(true, $request->secure());
    }

    public function testCookies()
    {
        $request = new Request([
            'cookies' => [
                'foo' => 'bar'
            ]
        ]);

        $this->assertEquals('foo', $request->cookies->get('foo')->name);
        $this->assertEquals('bar', $request->cookies->get('foo')->value);
    }
}
