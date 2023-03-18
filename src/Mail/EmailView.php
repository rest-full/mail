<?php

namespace Restfull\Mail;

use Restfull\Controller\BaseController;
use Restfull\Error\Exceptions;
use Restfull\Filesystem\File;

class EmailView
{

    public function run(BaseController $controller, string $layout, string $action): BaseController
    {
        $viewBuilder = $this->instance->resolveClass(
            $this->instance->assemblyClassOrPath(
                "%s" . DS_REVERSE . "Builders" . DS_REVERSE . '%sBuilder',
                [ROOT_NAMESPACE, MVC[1]]
            ),
            [
                'request' => $this->request,
                'response' => $this->response,
                'instance' => $this->instance,
                'data' => $controller->view ?? []
            ]
        );
        $viewBuilder->config(
            [
                'activeHelpers' => $controller->activeHelpers,
                'action' => $action,
                'encrypt' => $controller->encrypting
            ]
        )->render($this->pathView($action));
        $controller->response = $viewBuilder->responseView();
        return $controller;
    }

    /**
     * @param BaseController $controller
     *
     * @return array
     * @throws Exceptions
     */
    public function pathView(string $action): array
    {
        $layout = $this->instance->assemblyClassOrPath(
            '%s' . DS . 'Template' . DS . 'Layout' . DS . 'Email.phtml',
            [substr(str_replace('App', 'src', ROOT_APP), 0, -1)]
        );
        $pageContent = $this->instance->assemblyClassOrPath(
            '%s' . DS . "Template" . DS . "Email" . DS . "%s.phtml",
            [substr(str_replace('App', 'src', ROOT_APP), 0, -1), $action]
        );
        $file = $this->instance->resolveClass(
            $this->instance->assemblyClassOrPath(
                '%s' . DS_REVERSE . 'Filesystem' . DS_REVERSE . 'File',
                [ROOT_NAMESPACE]
            ),
            ['file' => $pageContent]
        );
        if (!$file->exists()) {
            throw new Exceptions(
                "The {$action} view wasn't found in the layout folder.",
                405
            );
        }
        return [$layout, $pageContent];
    }

}