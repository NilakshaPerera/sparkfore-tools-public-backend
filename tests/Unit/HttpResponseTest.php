<?php

namespace tests\Unit;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Tests\TestCase;
use App\Domain\Traits\HttpResponse;

class HttpResponseTest extends TestCase
{
    /**
     * Test sendResponse method of HttpResponse trait.
     */
    public function testSendResponse()
    {
        $mock = new class {
            use HttpResponse;
        };

        $data = ['name' => 'John Doe'];
        $message = 'OK';
        $statusCode = 200;
        $headers = ['X-Custom-Header' => 'Value'];

        Response::shouldReceive('json')->once()->andReturn(new JsonResponse([
            'status_code' => $statusCode,
            'status' => 'OK',
            'message' => $message,
            'result' => $data,
            'locale' => app()->getLocale(),
        ], $statusCode));

        $response = $mock->sendResponse($data, $message, $statusCode, $headers);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertEquals($message, $response->getData()->message);

        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals('Value', $response->headers->get('X-Custom-Header'));
    }

    /**
     * Test sendErrorResponse method of HttpResponse trait.
     */
    public function testSendErrorResponse()
    {
        $mock = new class {
            use HttpResponse;
        };

        $data = ['error' => 'Something went wrong'];
        $message = 'ERROR';
        $statusCode = 400;
        $headers = ['X-Custom-Header' => 'Value'];

        Response::shouldReceive('json')->once()->andReturn(new JsonResponse([
            'status_code' => $statusCode,
            'status' => 'ERROR',
            'message' => $message,
            'result' => $data,
        ], $statusCode));

        $response = $mock->sendErrorResponse($data, $message, $statusCode, $headers);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertEquals($message, $response->getData()->message);

        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals('Value', $response->headers->get('X-Custom-Header'));
    }

    /**
     * Test sendException method of HttpResponse trait.
     */
    public function testSendException()
    {
        $mock = new class {
            use HttpResponse;
        };

        $exception = new \Exception('Test Exception', 500);
        $statusCode = 500;
        $message = 'Test Exception';

        Response::shouldReceive('json')->once()->andReturn(new JsonResponse([
            'status_code' => $statusCode,
            'status' => 'Internal Server Error',
            'message' => $message,
        ], $statusCode));

        $response = $mock->sendException($exception);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    /**
     * Test setStatusCode method of HttpResponse trait.
     */
    public function testSetStatusCode()
    {
        $mock = new class {
            use HttpResponse;
        };

        $statusCode = 404;
        $statusText = 'Not Found';

        $mock->setStatusCode($statusCode, $statusText);

        $this->assertEquals($statusCode, $mock->getStatusCode());
        $this->assertEquals($statusText, $mock->getStatusText());
    }

    /**
     * Test getHttpHeaders method of HttpResponse trait.
     */
    public function testGetHttpHeaders()
    {
        $mock = new class {
            use HttpResponse;
        };

        $headers = $mock->getHttpHeaders();

        $this->assertIsArray($headers);
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertEquals('application/json', $headers['Content-Type']);
    }

    /**
     * Test setRedirect method of HttpResponse trait.
     */
    public function testSetRedirect()
    {
        $mock = new class {
            use HttpResponse;
        };

        $statusCode = 302;
        $url = 'http://example.com';
        $state = 'some-state';
        $error = 'error';
        $errorDescription = 'error-description';
        $errorUri = 'http://example.com/error';

        $mock->setRedirect($statusCode, $url, $state, $error, $errorDescription, $errorUri);

        $headers = $mock->getHttpHeaders();

        $this->assertArrayHasKey('Location', $headers);
        $this->assertEquals($url, $headers['Location']);
    }

    /**
     * Test setError method of HttpResponse trait.
     */
    public function testSetError()
    {
        $mock = new class {
            use HttpResponse;
        };

        $statusCode = 400;
        $error = 'invalid_request';
        $errorDescription = 'Invalid request parameters.';
        $errorUri = '#section-4.2';

        $mock->setError($statusCode, $error, $errorDescription, $errorUri);

        $headers = $mock->getHttpHeaders();

        $this->assertArrayHasKey('Cache-Control', $headers);
        $this->assertEquals('no-store', $headers['Cache-Control']);
    }

    /**
     * Test isSuccessful method of HttpResponse trait.
     */
    public function testIsSuccessful()
    {
        $mock = new class {
            use HttpResponse;
        };

        $statusCode = 200;

        $mock->setStatusCode($statusCode);

        $this->assertTrue($mock->isSuccessful());
    }

    /**
     * Test isClientError method of HttpResponse trait.
     */
    public function testIsClientError()
    {
        $mock = new class {
            use HttpResponse;
        };

        $statusCode = 404;

        $mock->setStatusCode($statusCode);

        $this->assertTrue($mock->isClientError());
    }

    /**
     * Test isServerError method of HttpResponse trait.
     */
    public function testIsServerError()
    {
        $mock = new class {
            use HttpResponse;
        };

        $statusCode = 500;

        $mock->setStatusCode($statusCode);

        $this->assertTrue($mock->isServerError());
    }

    /**
     * Test isInvalid method of HttpResponse trait.
     * @expectedException InvalidArgumentException::class
     */
    public function testIsInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);

        $mock = new class {
            use HttpResponse;
        };

        // Test with valid status code
        $mock->setStatusCode(200);
        $mock->isRedirection();
        $mock->isInformational();
        $mock->send();
        $mock->getResponseBody('json');
        $this->assertFalse($mock->isInvalid());

        // Test with invalid status code
        $mock->setStatusCode(700);

        $mock->isInvalid();
    }

    /**
     * Test sendAPIErrorResponse method of HttpResponse trait.
     */
    public function testSendApiErrorResponse()
    {
        $mock = new class {
            use HttpResponse;
        };

        $data = ['error' => 'Something went wrong'];
        $message = 'ERROR';
        $statusCode = 400;
        $headers = ['X-Custom-Header' => 'Value'];

        Response::shouldReceive('json')->once()->andReturn(new JsonResponse([
            'status_code' => $statusCode,
            'status' => 'ERROR',
            'message' => $message,
            'result' => $data,
        ], $statusCode));

        $response = $mock->sendAPIErrorResponse($data, $statusCode, $headers);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertEquals($message, $response->getData()->message);

        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals('Value', $response->headers->get('X-Custom-Header'));
    }

}
