<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $exception = $event->getThrowable();
        
        // Si c'est une requête AJAX ou si on attend du JSON
        if ($request->isXmlHttpRequest() || $request->getPreferredFormat() === 'json') {
            $statusCode = $exception instanceof HttpException ? $exception->getStatusCode() : 500;
            
            $response = new JsonResponse([
                'success' => false,
                'message' => $exception->getMessage()
            ], $statusCode);
            
            $event->setResponse($response);
        }
    }
}