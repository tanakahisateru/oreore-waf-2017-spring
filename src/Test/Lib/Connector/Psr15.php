<?php
namespace My\Web\Test\Lib\Connector;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\BrowserKit\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\UploadedFile;
use Zend\Stratigility\Delegate\CallableDelegateDecorator;
use Zend\Stratigility\NoopFinalHandler;

class Psr15 extends Client
{
    /**
     * @var callable
     */
    protected $processor;

    /**
     * @param MiddlewareInterface|callable $processor
     */
    public function setProcessor($processor)
    {
        $this->processor = $processor;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function doRequest($request)
    {
        $inputStream = fopen('php://memory', 'r+');
        $content = $request->getContent();
        if ($content !== null) {
            fwrite($inputStream, $content);
            rewind($inputStream);
        }

        $queryParams = [];
        $postParams = [];
        $queryString = parse_url($request->getUri(), PHP_URL_QUERY);
        if ($queryString != '') {
            parse_str($queryString, $queryParams);
        }
        if ($request->getMethod() !== 'GET') {
            $postParams = $request->getParameters();
        }

        $serverParams = $request->getServer();
        if (!isset($serverParams['SCRIPT_NAME'])) {
            //required by WhoopsErrorHandler
            $serverParams['SCRIPT_NAME'] = 'Codeception';
        }

        /** @var ServerRequestInterface $psr7Request */
        $psr7Request = new ServerRequest(
            $serverParams,
            $this->convertFiles($request->getFiles()),
            $request->getUri(),
            $request->getMethod(),
            $inputStream,
            $this->extractHeaders($request)
        );

        $psr7Request = $psr7Request->withQueryParams($queryParams);
        $psr7Request = $psr7Request->withParsedBody($postParams);
        $psr7Request = $psr7Request->withCookieParams($request->getCookies());

        $cwd = getcwd();
        chdir(codecept_root_dir());

        $psr7ResponsePrototype = new \Zend\Diactoros\Response();
        if ($this->processor instanceof MiddlewareInterface) {
            $delegate = new CallableDelegateDecorator(new NoopFinalHandler(), $psr7ResponsePrototype);
            $psr7Response = $this->processor->process($psr7Request, $delegate);
        } else {
            $psr7Response = call_user_func($this->processor, $psr7Request, $psr7ResponsePrototype);
        }

        chdir($cwd);

        $this->request = $psr7Request;

        return new Response(
            strval($psr7Response->getBody()),
            $psr7Response->getStatusCode(),
            $psr7Response->getHeaders()
        );
    }

    private function convertFiles(array $files)
    {
        $fileObjects = [];
        foreach ($files as $fieldName => $file) {
            if ($file instanceof UploadedFileInterface) {
                $fileObjects[$fieldName] = $file;
            } elseif (!isset($file['tmp_name']) && !isset($file['name'])) {
                $fileObjects[$fieldName] = $this->convertFiles($file);
            } else {
                $fileObjects[$fieldName] = new UploadedFile(
                    $file['tmp_name'],
                    $file['size'],
                    $file['error'],
                    $file['name'],
                    $file['type']
                );
            }
        }
        return $fileObjects;
    }

    private function extractHeaders(Request $request)
    {
        $headers = [];
        $server = $request->getServer();

        $contentHeaders = array('Content-Length' => true, 'Content-Md5' => true, 'Content-Type' => true);
        foreach ($server as $header => $val) {
            $header = implode('-', array_map('ucfirst', explode('-', strtolower(str_replace('_', '-', $header)))));

            if (strpos($header, 'Http-') === 0) {
                $headers[substr($header, 5)] = $val;
            } elseif (isset($contentHeaders[$header])) {
                $headers[$header] = $val;
            }
        }

        return $headers;
    }
}
